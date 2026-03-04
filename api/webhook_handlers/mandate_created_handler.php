<?php
namespace WebhookHandlers;

use \Models\Country;
use \Models\Member;
use \Models\MemberName;
use Models\Mandate;

class MandateCreatedHandler extends AbstractWebhookHandler {

    const PENDING_MEMBERSHIP_STATUS_ID = 7;

    /**
     * Handle mandates.created event.
     * If an exact match already exists (name + email + postcode), link mandate to that member.
     * Otherwise create a new pending member.
     * @param object $event Event object from GoCardless library
     * @param \Models\WebhookLog $webhook_log
     * @return array
     */
    public function handle($event, $webhook_log) {
        $mandate_id = $event->links->mandate ?? null;
        if (empty($mandate_id)) {
            throw new \Exception('Missing mandate ID in event');
        }

        $mandate_data = $this->getMandateDetails($mandate_id);
        $customer_id = $mandate_data->links->customer ?? null;
        if (empty($customer_id)) {
            throw new \Exception('Missing customer ID in mandate data');
        }

        $customer = $this->getCustomerDetails($customer_id);
        $customer_data = $this->extractCustomerData($customer);
        $this->validateCustomerData($customer_data);

        $country_id = $this->resolveCountryId($customer_data['country_code'], $customer_id);
        $gps_coords = $this->lookupGPSCoordinates($customer_data['postcode']);

        if ($gps_coords) {
            error_log("Found GPS coordinates for postcode {$customer_data['postcode']}: lat={$gps_coords['lat']}, lng={$gps_coords['lng']}");
        } else {
            error_log("No GPS coordinates found for postcode: {$customer_data['postcode']}");
        }

        $member_id = $this->findExactMatchingMemberId(
            $customer_data['given_name'],
            $customer_data['family_name'],
            $customer_data['email'],
            $customer_data['postcode']
        );

        if ($member_id) {
            error_log("Matched existing member ID $member_id for mandate $mandate_id");
            $this->updateMemberMandateReference($member_id, $mandate_id);
        } else {
            $member_id = $this->createMemberFromCustomer($customer_data, $country_id, $mandate_id, $gps_coords);
        }

        $this->upsertMandateRecord($member_id, $mandate_id, $customer_id);
        $webhook_log->markProcessed($member_id);

        return [
            'event_id' => $event->id,
            'status' => 'success',
            'member_id' => $member_id,
            'mandate_id' => $mandate_id
        ];
    }

    /**
     * Map GoCardless customer object to internal customer array.
     * @param object $customer
     * @return array
     */
    protected function extractCustomerData($customer) {
        return [
            'given_name' => trim($customer->given_name ?? ''),
            'family_name' => trim($customer->family_name ?? ''),
            'company_name' => trim($customer->company_name ?? ''),
            'email' => trim($customer->email ?? ''),
            'country_code' => trim($customer->country_code ?? ''),
            'address_line1' => trim($customer->address_line1 ?? ''),
            'address_line2' => trim($customer->address_line2 ?? ''),
            'city' => trim($customer->city ?? ''),
            'county' => trim($customer->region ?? ''),
            'postcode' => trim($customer->postal_code ?? '')
        ];
    }

    /**
     * Validate minimum required fields from GoCardless customer data.
     * @param array $customer_data
     * @return void
     */
    protected function validateCustomerData(array $customer_data) {
        if (empty($customer_data['company_name']) && empty($customer_data['given_name']) && empty($customer_data['family_name'])) {
            throw new \Exception('Missing customer name and given/family name in GoCardless data');
        }

        if (empty($customer_data['email'])) {
            throw new \Exception('Missing email in GoCardless customer data');
        }
    }

    /**
     * Resolve country code from GoCardless to internal country ID.
     * @param string $country_code
     * @param string $customer_id
     * @return int
     */
    protected function resolveCountryId($country_code, $customer_id) {
        try {
            $country = Country::getInstance()->setCode($country_code)->readOne();
            $country_id = $country['id'] ?? 0;
            if ($country_id === 0) {
                throw new \Exception("No matching country found for code: $country_code");
            }
            error_log("Fetched country $country_code from database for customer $customer_id");
            return (int) $country_id;
        } catch (\Exception $e) {
            throw new \Exception("Failed to fetch country from database: " . $e->getMessage());
        }
    }

    /**
     * Find exact member match using firstname + surname + email + postcode.
     * Returns null unless all four fields are present.
     * @param string $given_name
     * @param string $family_name
     * @param string $email
     * @param string $postcode
     * @return int|null
     */
    protected function findExactMatchingMemberId($given_name, $family_name, $email, $postcode) {
        $given_name = $this->normalizeText($given_name);
        $family_name = $this->normalizeText($family_name);
        $email = $this->normalizeText($email);
        $postcode = $this->normalizePostcode($postcode);

        if (empty($given_name) || empty($family_name) || empty($email) || empty($postcode)) {
            return null;
        }

        $query = "SELECT m.idmember
                  FROM member m
                  INNER JOIN membername mn ON m.idmember = mn.member_idmember
                  WHERE LOWER(TRIM(mn.firstname)) = :given_name
                    AND LOWER(TRIM(mn.surname)) = :family_name
                    AND LOWER(TRIM(m.email1)) = :email
                    AND REPLACE(LOWER(TRIM(m.postcode)), ' ', '') = :postcode
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            $errorInfo = $this->conn->errorInfo();
            throw new \Exception("Failed to prepare member match statement: " . ($errorInfo[2] ?? 'Unknown error'));
        }

        $stmt->bindParam(':given_name', $given_name);
        $stmt->bindParam(':family_name', $family_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':postcode', $postcode);

        if (!$stmt->execute()) {
            $errorInfo = $stmt->errorInfo();
            throw new \Exception("Failed to execute member match statement: " . ($errorInfo[2] ?? 'Unknown error'));
        }

        if ($stmt->rowCount() === 0) {
            return null;
        }

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return isset($row['idmember']) ? (int) $row['idmember'] : null;
    }

    /**
     * Update existing member with the latest mandate ID, so downstream handlers can resolve it.
     * @param int $member_id
     * @param string $mandate_id
     * @return void
     */
    protected function updateMemberMandateReference($member_id, $mandate_id) {
        $query = "UPDATE member
                  SET bankpayerref = :mandate_id,
                      username = :username
                  WHERE idmember = :member_id
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            $errorInfo = $this->conn->errorInfo();
            throw new \Exception("Failed to prepare member update statement: " . ($errorInfo[2] ?? 'Unknown error'));
        }

        $clean_mandate_id = htmlspecialchars(strip_tags($mandate_id));
        $username = 'gocardless_webhook';
        $stmt->bindParam(':mandate_id', $clean_mandate_id);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':member_id', $member_id);

        if (!$stmt->execute()) {
            $errorInfo = $stmt->errorInfo();
            throw new \Exception("Failed to update member mandate reference: " . ($errorInfo[2] ?? 'Unknown error'));
        }
    }

    /**
     * Create a new pending member from customer data.
     * @param array $customer_data
     * @param int $country_id
     * @param string $mandate_id
     * @param array|null $gps_coords
     * @return int
     */
    protected function createMemberFromCustomer(array $customer_data, $country_id, $mandate_id, $gps_coords) {
        $member = new Member();

        $member->businessname = $customer_data['company_name'];
        $member->bankpayerref = $mandate_id;
        $member->email1 = $customer_data['email'];
        $member->addressfirstline = $customer_data['address_line1'];
        $member->addresssecondline = $customer_data['address_line2'];
        $member->city = $customer_data['city'];
        $member->county = $customer_data['county'];
        $member->postcode = $customer_data['postcode'];
        $member->countryID = $country_id;
        $member->joindate = date('Y-m-d');
        $member->username = 'gocardless_webhook';
        $member->statusID = self::PENDING_MEMBERSHIP_STATUS_ID;

        $member->title = '';
        $member->note = '';
        $member->area = '';
        $member->phone1 = '';
        $member->addressfirstline2 = '';
        $member->addresssecondline2 = '';
        $member->city2 = '';
        $member->county2 = '';
        $member->postcode2 = '';
        $member->country2ID = 0;
        $member->email2 = '';
        $member->phone2 = '';
        $member->expirydate = null;
        $member->reminderdate = null;
        $member->deletedate = null;
        $member->repeatpayment = 0;
        $member->recurringpayment = 0;
        $member->gdpr_email = false;
        $member->gdpr_tel = false;
        $member->gdpr_address = false;
        $member->gdpr_sm = false;
        $member->postonhold = false;
        $member->emailonhold = false;
        $member->multiplier = 1;
        $member->membershipfee = 0;
        $member->gpslat1 = $gps_coords['lat'] ?? null;
        $member->gpslng1 = $gps_coords['lng'] ?? null;
        $member->gpslat2 = null;
        $member->gpslng2 = null;

        if (!$member->create()) {
            throw new \Exception('Failed to create GoCardless member record');
        }

        $member_id = (int) $member->id;
        error_log("Created member ID $member_id with mandate $mandate_id");
        $this->createMemberNameIfPossible($member_id, $customer_data['given_name'], $customer_data['family_name']);

        return $member_id;
    }

    /**
     * Create membername row for personal names when both first and last names are present.
     * @param int $member_id
     * @param string $given_name
     * @param string $family_name
     * @return void
     */
    protected function createMemberNameIfPossible($member_id, $given_name, $family_name) {
        if (empty($given_name) || empty($family_name)) {
            return;
        }

        $memberName = new MemberName();
        $memberName->honorific = '';
        $memberName->firstname = $given_name;
        $memberName->surname = $family_name;
        $memberName->idmember = $member_id;

        if ($memberName->create()) {
            error_log("Created member name (ID: {$memberName->id}) for member $member_id: $given_name $family_name");
            return;
        }

        error_log("Warning: Failed to create member name for member $member_id");
    }

    /**
     * Upsert GoCardless mandate mapping for member.
     * @param int $member_id
     * @param string $mandate_id
     * @param string $customer_id
     * @return void
     */
    protected function upsertMandateRecord($member_id, $mandate_id, $customer_id) {
        $mandate = new Mandate();
        $mandate->idmember = $member_id;
        $mandate->gc_mandate_id = $mandate_id;
        $mandate->gc_customer_id = $customer_id;
        $mandate->gc_subscriptionid = '';

        if ($mandate->exists($mandate_id)) {
            if ($mandate->update()) {
                error_log("Updated GoCardless mandate record for member ID $member_id");
                return;
            }
            throw new \Exception('Failed to update GoCardless mandate record');
        }

        if ($mandate->create()) {
            error_log("Created GoCardless mandate record for member ID $member_id");
            return;
        }

        throw new \Exception('Failed to create GoCardless mandate record');
    }

    /**
     * Lower-case and trim free text.
     * @param string $value
     * @return string
     */
    protected function normalizeText($value) {
        return strtolower(trim((string) $value));
    }

    /**
     * Lower-case, trim, and strip spaces from postcode for robust exact matching.
     * @param string $postcode
     * @return string
     */
    protected function normalizePostcode($postcode) {
        return str_replace(' ', '', $this->normalizeText($postcode));
    }

    /**
     * Look up GPS coordinates from osdata table by postcode
     * @param string $postcode Postcode with spaces (e.g., "SW1A 1AA")
     * @return array|null ['lat' => float, 'lng' => float] or null if not found
     */
    protected function lookupGPSCoordinates($postcode) {
        if (empty($postcode)) {
            return null;
        }

        // Trim whitespace but preserve internal spaces
        $postcode = trim($postcode);

        try {
            $query = "SELECT gpslat, gpslng
                      FROM osdata
                      WHERE postcode = :postcode
                      LIMIT 1";

            $stmt = $this->conn->prepare($query);

            if (!$stmt) {
                error_log("Failed to prepare osdata query: " . print_r($this->conn->errorInfo(), true));
                return null;
            }

            $stmt->bindParam(':postcode', $postcode);

            if (!$stmt->execute()) {
                error_log("Failed to execute osdata query: " . print_r($stmt->errorInfo(), true));
                return null;
            }

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                return [
                    'lat' => $row['gpslat'],
                    'lng' => $row['gpslng']
                ];
            }

            return null;
        } catch (\Exception $e) {
            error_log("Error looking up GPS coordinates for postcode $postcode: " . $e->getMessage());
            return null;
        }
    }
}
