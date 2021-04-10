import {BankAccount} from './bank-account';
export class TransactionSummary {

    index: string;
    count: number;
    sum: number;
    bankID: number;
    
    constructor(obj?: any) {

        this.index = obj && obj.id || null;
        this.count = obj && obj.count || null;
        this.sum = obj && obj.sum || null;
        this.bankID = obj && obj.bankID || null;
    }
}