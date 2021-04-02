 export class MemberInvalidEmail {

    id: number;
    membershiptype: string;
    name: string;
    businessname: string;
    email1: string;
    email2: string;
    
    constructor(obj?: any) {

        this.id = obj && obj.id || null;        
        this.membershiptype = obj && obj.membershiptype || null;
        this.name = obj && obj.name || null;
        this.businessname = obj && obj.businessname || null;
        this.email1 = obj && obj.email1 || null;
        this.email2 = obj && obj.email2 || null;
    }   
}