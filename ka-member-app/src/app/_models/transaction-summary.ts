import {BankAccount} from './bank-account';
export class TransactionSummary {

    index: string;
    count?: number;
    sum?: number;
    bank?: BankAccount;
    
    constructor(obj?: any) {

        this.index = obj && obj.id || null;
        this.count = obj && obj.count || null;
        this.sum = obj && obj.sum || null;

        if (obj && obj.bankID && obj.bankaccount) {
            this.bank = new BankAccount({id: obj.bankID, name: obj.bankaccount});            
        }
    }
}