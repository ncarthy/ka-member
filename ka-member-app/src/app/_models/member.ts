/**
 * MemberSearchResult is a data-structure that holds an individual
 * record from a Member database search
 */
export class Member {

    id: number;
    membershiptype: string;
    name: string;
    businessname: string;
    note: string;
    addressfirstline: string;
    addresssecondline: string;
    city: string;
    postcode: string;
    country: string;    
    reminderdate: Date;
    deletedate: Date;
    lasttransactiondate: Date;
    email: Date;
    
    constructor(obj?: any) {

        this.id = obj && obj.id || null;
        this.membershiptype = obj && obj.membershiptype || null;
        this.name = obj && obj.name || null;
        this.businessname = obj && obj.businessname || null;
        this.note = obj && obj.note || null;
        this.addressfirstline = obj && obj.addressfirstline || null;
        this.addresssecondline = obj && obj.addresssecondline || null;
        this.city = obj && obj.city || null;
        this.postcode = obj && obj.postcode || null;
        this.country = obj && obj.country || null;
        this.reminderdate = obj && obj.reminderdate || null;
        this.deletedate = obj && obj.deletedate || null;
        this.lasttransactiondate = obj && obj.lasttransactiondate || null;
        this.email = obj && obj.email || null;
    }
}