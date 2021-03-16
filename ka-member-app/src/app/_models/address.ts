import { Country } from "./country";

/*
AddressApiResponse holds the object received from https://getaddress.io

It is of the form:
{
    "latitude":51.50004577636719,
    "longitude":-0.16724194586277008,
    "addresses":[
        ["17 Montpelier Square","","","London",""],
        ["17a Montpelier Square","","","London",""],
        ["18 Montpelier Square","","","London",""],
        ["19 Montpelier Square","","","London",""],
        ["20 Montpelier Square","","","London",""],
        ["21 Montpelier Square","","","London",""],
        ["22 Montpelier Square","","","London",""],
        ["23 Montpelier Square","","","London",""],
        ["24 Montpelier Square","","","London",""],
        ["25 Montpelier Square","","","London",""],
        ["Flat 1","7 Montpelier Square","","London",""]]}

The 'addresses' property contains an array of addresses representing 
every address in the postcode:
{
    "latitude": 52.24593734741211,
    "longitude": -0.891636312007904,
    "addresses":["Line1","Line2","Line3",Town/City,County"]
}

*/
export class Address {
    line1?: string;    
    line2?: string;
    line3?: string;
    town?: string;
    county?: string;
    country: Country = new Country();
    postcode: string = '';

    // From https://stackoverflow.com/a/37682352/6941165
    public constructor(init?:Partial<Address>) {
        Object.assign(this, init);
    }
}