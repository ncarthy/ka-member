export class Transaction {

    id: number;
    date: string;
    amount: string;
    paymenttypeID?: number;
    idmember: number;
    bankID?: number;
    note?: string;
    
    constructor(obj?: any) {

        this.id = obj && obj.id || null;
        this.date = obj && obj.date || null;
        this.amount = obj && obj.amount || null;
        this.paymenttypeID = obj && obj.paymenttypeID || null;
        this.note = obj && obj.note || null;
        this.bankID = obj && obj.bankID || null;
        this.idmember = obj && obj.idmember || null;
    }
}