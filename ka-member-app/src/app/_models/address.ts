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