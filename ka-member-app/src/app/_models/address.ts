export class Address {
    addressfirstline: string;
    addresssecondline: string;
    city: string;
    county: string;
    country: number;
    postcode: string;

    constructor(obj?: any) {

        this.addressfirstline = obj && obj.addressfirstline || null;
        this.addresssecondline = obj && obj.addresssecondline || null;
        this.city = obj && obj.city || null;
        this.county = obj && obj.county || null;
        this.country = obj && obj.country || null;
        this.postcode = obj && obj.postcode || null;
    }


}

export function AddresstoHTML(address: Address) : string {
    let s= address.addressfirstline;
    if (address.addresssecondline) {
        s=s.concat(', ', address.addresssecondline);
    }
    if (address.city) {
        s=s.concat("\r\n", address.city);
    }
    if (address.county) {
        s=s.concat(", ", address.county);
    }
    if (address.postcode) {
        s=s.concat(", ", address.postcode);
    }
    return s;
}