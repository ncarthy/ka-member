import {Country} from '@app/_models';

export interface AddressFormValue {
  addressLine1: string;
  addressLine2?: string;
  city: string;
  county?: string;
  country: Country;
  postcode: string;
}

export function addressFormValueToHTML(addressFormValue: AddressFormValue): string {
  if (!addressFormValue) {
    return '';
  }
  return (
    addressFormValue.addressLine1 +
    ' ' +
    addressFormValue.addressLine2 +
    '<br>' +
    addressFormValue.city +
    ', ' +
    addressFormValue.county +
    ', ' +
    addressFormValue.country.name +
    '<br>' +
    addressFormValue.postcode
  );
}
