import { Address,Country } from '@app/_models';
/**
 * Member is a data-structure that holds all the information stored
 * about the member in the database
 */
export class Member {

    id: number;
    title: string;
    businessname: string;
    bankpayerref: string;
    note: string;
    email1: string;
    phone1: string;
    email2: string;
    phone2: string; 
    statusID: number;
    expirydate: Date;
    joindate: Date;
    reminderdate: Date;
    updatedate: Date;
    deletedate: Date;
    repeatpayment: number;
    recurringpayment: number;
    username: string;
    gdpr_email: boolean;
    gdpr_tel: boolean;
    gdpr_address: boolean;
    gdpr_sm: boolean;
    postonhold: boolean;
    primaryAddress: Address;
    secondaryAddress: Address;
    isDeleting: boolean = false;
    isUpdating: boolean = false;
    
    constructor(obj?: any) {

        this.id = obj && obj.id || null;
        this.title = obj && obj.title || null;
        this.businessname = obj && obj.businessname || null;
        this.bankpayerref = obj && obj.bankpayerref || null;
        this.note = obj && obj.note || null;
        this.email1 = obj && obj.email1 || null;
        this.phone1 = obj && obj.phone1 || null;
        this.email2 = obj && obj.email2 || null;
        this.phone2 = obj && obj.phone2 || null;
        this.statusID = obj && obj.statusID || null;
        this.expirydate = obj && obj.expirydate || null;
        this.reminderdate = obj && obj.reminderdate || null;
        this.joindate = obj && obj.joindate || null;
        this.updatedate = obj && obj.updatedate || null;
        this.deletedate = obj && obj.deletedate || null;
        this.repeatpayment = obj && obj.repeatpayment || null;
        this.recurringpayment = obj && obj.recurringpayment || null;
        this.username = obj && obj.username || null;
        this.gdpr_email = obj && obj.gdpr_email;
        this.gdpr_tel = obj && obj.gdpr_tel;
        this.gdpr_address = obj && obj.gdpr_address;
        this.gdpr_sm = obj && obj.gdpr_sm;
        this.postonhold = obj && obj.postonhold;
        this.primaryAddress = obj && obj.primaryAddress || null;
        this.secondaryAddress = obj && obj.secondaryAddress || null;
    }
}