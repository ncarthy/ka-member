<?php
namespace WebhookHandlers;

use \Models\Country;
use \Models\Member;
use \Models\MemberName;

class MandateCreatedHandler extends AbstractWebhookHandler {

    const PENDING_MEMBERSHIP_STATUS_ID = 7;

    /**
     * Handle mandates.created event - creates new member record
     * Fetches mandate and customer data from GoCardless API
     * @param object $event Event object from GoCardless library
     * @param \Models\WebhookLog $webhook_log
     * @return array
     */
    public function handle($event, $webhook_log) {
        $mandate_id = $event->links->mandate ?? null;

        if (empty($mandate_id)) {
            throw new \Exception('Missing mandate ID in event');
        }

        // Step 1: Fetch mandate details from GoCardless API, to get customer ID
        $mandate = $this->getMandateDetails($mandate_id);
        $customer_id = $mandate->links->customer ?? null;
        if (empty($customer_id)) {
            throw new \Exception('Missing customer ID in mandate data');
        }

        try {
            $customer = $this->client->customers()->get($customer_id);
            error_log("Fetched customer $customer_id from GoCardless API for mandate $mandate_id");
        } catch (\Exception $e) {
            throw new \Exception("Failed to fetch customer from GoCardless API: " . $e->getMessage());
        }

        // Step 2: Fetch custoimer details from GoCardless API and extract customer details
        $customer = $this->getCustomerDetails($customer_id);
        $given_name = $customer->given_name ?? '';
        $family_name = $customer->family_name ?? '';
        $company_name = $customer->company_name ?? '';
        $email = $customer->email ?? '';
        $country_code = $customer->country_code ?? '';

        try {
            $country = Country::getInstance()->setCode($country_code)->readOne();
            $country_id = $country['id'] ?? 0;
            if ($country_id === 0) {
                throw new \Exception("No matching country found for code: $country_code");
            }
            error_log("Fetched country $country_code from database for customer $customer_id");
        } catch (\Exception $e) {
            throw new \Exception("Failed to fetch country from database: " . $e->getMessage());
        }

        if (empty($company_name) && empty($given_name) && empty($family_name)) {
            throw new \Exception('Missing customer name and Given and Family Name in GoCardless data');
        }

        if (empty($email)) {
            throw new \Exception('Missing email in GoCardless customer data');
        }

        // Extract address from customer
        $address_line1 = $customer->address_line1 ?? '';
        $address_line2 = $customer->address_line2 ?? '';
        $city = $customer->city ?? '';
        $county = $customer->region ?? ''; // GoCardless uses 'region' for county/state
        $postcode = $customer->postal_code ?? '';

        // Create new member record using Member model
        $member = new Member();

        // Set required fields
        $member->businessname = $company_name;
        $member->bankpayerref = $mandate_id;
        $member->email1 = $email;
        $member->addressfirstline = $address_line1;
        $member->addresssecondline = $address_line2;
        $member->city = $city;
        $member->county = $county;
        $member->postcode = $postcode;
        $member->countryID = $country_id;
        $member->joindate = date('Y-m-d'); // CURDATE() equivalent
        $member->username = 'gocardless_webhook';
        $member->statusID = self::PENDING_MEMBERSHIP_STATUS_ID;

        // Set default values for other fields
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
        $member->gpslat1 = null;
        $member->gpslat2 = null;
        $member->gpslng1 = null;
        $member->gpslng2 = null;

        if ($member->create()) {
            $member_id = $member->id;
            error_log("Created member ID $member_id with mandate $mandate_id");

            // Add member name if given name and family name are provided
            if (!empty($given_name) && !empty($family_name)) {
                $memberName = new MemberName();
                $memberName->honorific = ''; // No honorific from GoCardless
                $memberName->firstname = $given_name;
                $memberName->surname = $family_name;
                $memberName->idmember = $member_id;

                if ($memberName->create()) {
                    error_log("Created member name (ID: {$memberName->id}) for member $member_id: $given_name $family_name");
                } else {
                    error_log("Warning: Failed to create member name for member $member_id");
                    // Don't fail the whole operation if member name creation fails
                }
            }

            // Mark webhook as processed with member ID
            $webhook_log->markProcessed($member_id);

            return [
                'event_id' => $event->id,
                'status' => 'success',
                'member_id' => $member_id,
                'mandate_id' => $mandate_id
            ];
        } else {
            throw new \Exception('Failed to create member record using Member model');
        }
    }
}
