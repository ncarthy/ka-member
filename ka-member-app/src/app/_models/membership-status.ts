export class MembershipStatus {
    id: number;
    name: string;    
    multiplier: number;
    membershipfee: number;

    constructor(obj?: any) {

        this.id = obj && obj.id || null;
        this.name = obj && obj.name || null;
        this.multiplier = obj && obj.multiplier || null;
        this.membershipfee = obj && obj.membershipfee || null;
    }
}