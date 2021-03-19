export class MemberName {
    id: number;
    honorific?: string;    
    firstname?: string;    
    surname: string;    
    idmember: number

    constructor(obj?: any) {

        this.id = obj && obj.id || null;
        this.honorific = obj && obj.honorific || null;
        this.firstname = obj && obj.firstname || null;
        this.surname = obj && obj.surname || null;
        this.idmember = obj && obj.idmember || null;
    }
}