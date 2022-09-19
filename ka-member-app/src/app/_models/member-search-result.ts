/**
 * MemberSearchResult is a data-structure that holds an individual
 * record from a Member database search
 */
export class MemberSearchResult {
  id: number;
  membershiptype: string;
  name: string;
  title: string;
  businessname: string;
  note: string;
  addressfirstline: string;
  addresssecondline: string;
  city: string;
  postcode: string;
  country: string;
  deletedate: string | null;
  reminderdate: string | null;
  lasttransactiondate: string | null;
  count: number;
  paymenttype: string;
  bankaccount: string;
  email: string;
  postonhold: boolean;
  amount: boolean;
  gpslat: number;
  gpslong: number;
  isDeleting: boolean = false;
  isUpdating: boolean = false;

  constructor(obj?: any) {
    this.id = (obj && obj.id) || null;

    this.membershiptype = (obj && obj.membershiptype) || null;

    this.name = (obj && obj.name) || null;
    this.title = (obj && obj.title) || null;
    this.businessname = (obj && obj.businessname) || null;

    this.note = (obj && obj.note) || null;
    this.addressfirstline = (obj && obj.addressfirstline) || null;
    this.addresssecondline = (obj && obj.addresssecondline) || null;
    this.city = (obj && obj.city) || null;
    this.postcode = (obj && obj.postcode) || null;
    this.country = (obj && obj.country) || null;
    this.deletedate =
      (obj && obj.deletedate && this.convertDateToUKLocale(obj.deletedate)) ||
      null;
    this.reminderdate =
      (obj &&
        obj.reminderdate &&
        this.convertDateToUKLocale(obj.reminderdate)) ||
      null;
    this.lasttransactiondate =
      (obj &&
        obj.lasttransactiondate &&
        this.convertDateToUKLocale(obj.lasttransactiondate)) ||
      null;
    this.email = (obj && obj.email) || null;
    this.paymenttype = (obj && obj.paymenttype) || null;
    this.bankaccount = (obj && obj.bankaccount) || null;
    this.count = (obj && obj.count) || 0;
    this.postonhold = obj && obj.postonhold;
    this.amount = (obj && obj.amount) || 0;
    this.gpslat = (obj && obj.gpslat) || null;
    this.gpslong = (obj && obj.gpslong) || null;
  }

  convertDateToUKLocale(m: string): string {
    const t: string[] = m.split(/[- :]/);
    const d = new Date(Date.UTC(+t[0], +t[1] - 1, +t[2]));
    return d.toLocaleDateString('en-GB');
  }

  get addressToHTML(): string {
    return this.addressfirstline + '<br>' + this.addresssecondline
      ? this.addresssecondline + '<br>'
      : '' + this.city + '<br>' + this.postcode;
  }
}
