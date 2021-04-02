 export class MemberInvalidPostcode {

    id: number;
    membershiptype: string;
    name: string;
    businessname: string;
    postcode1: string;
    postcode2: string;
    
    constructor(obj?: any) {

        this.id = obj && obj.id || null;        
        this.membershiptype = obj && obj.membershiptype || null;
        this.name = obj && obj.name || null;
        this.businessname = obj && obj.businessname || null;
        this.postcode1 = obj && obj.postcode1 || null;
        this.postcode2 = obj && obj.postcode2 || null;
    }   
}