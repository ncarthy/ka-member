export class Address {
  idmember: number;
  addressfirstline: string;
  addresssecondline: string;
  city: string;
  county: string;
  country: number;
  postcode: string;
  lat: number;
  lng: number;

  constructor(obj?: any) {
    this.addressfirstline = (obj && obj.addressfirstline) || null;
    this.addresssecondline = (obj && obj.addresssecondline) || null;
    this.city = (obj && obj.city) || null;
    this.county = (obj && obj.county) || null;
    this.country = (obj && obj.country) || null;
    this.postcode = (obj && obj.postcode) || null;
    this.idmember = (obj && obj.idmember) || null;
    this.lat = (obj && obj.lat) || null;
    this.lng = (obj && obj.lng) || null;
  }

  public toString(): string {
    return (
      this.addressfirstline +
      ' ' +
      (this.addresssecondline?this.addresssecondline + ' ':'') +
      this.city +
      ' ' +
      this.postcode +
      ' ' +
      this.country
    );
  }
}

export function AddresstoHTML(address: Address): string {
  let s = address.addressfirstline;
  if (address.addresssecondline) {
    s = s.concat(', ', address.addresssecondline);
  }
  if (address.city) {
    s = s.concat('\r\n', address.city);
  }
  if (address.county) {
    s = s.concat(', ', address.county);
  }
  if (address.postcode) {
    s = s.concat(', ', address.postcode);
  }
  return s;
}
