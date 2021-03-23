import {YesNoAny} from '@app/_models/yes-no.enum';

/**
 * MemberFilter is the definition of 
 */
 export class MemberFilter {

    removed: YesNoAny = YesNoAny.ANY;

    surname?: string;
    notsurname?: string;
    businessname?: string;
    businessorsurname?: string;
    membertypeid?: number;
    countryid?: number;
    email1?: YesNoAny;
    postonhold?: YesNoAny;
    address?: string;
    paymentmethodID?: number;
    bankaccountID?: number;
    joindatestart?: Date;
    joindateend?: Date;
    expirydatestart?: Date;
    expirydateend?: Date;
    reminderdatestart?: Date;
    reminderdateend?: Date;
    updatedatestart?: Date;
    updatedateend?: Date;
    lasttransactiondatestart?: Date;
    lasttransactiondateend?: Date;
    deletedatestart?: Date;
    deletedateend?: Date;
    maxresults?: number;

    /* overload toString */
    /* From https://stackoverflow.com/a/35361695/6941165 */
    public toString = () : string => {

        var str = 'removed='+this.removed.toString();
        
        if (this.surname) {
            str = str.concat('&','surname=',this.surname)
        }
        if (this.notsurname) {
            str = str.concat('&','notsurname=',this.notsurname)
        }
        if (this.businessname) {
            str = str.concat('&','businessname=',this.businessname)
        }
        if (this.businessorsurname) {
            str = str.concat('&','businessorsurname=',this.businessorsurname)
        }
        if (this.membertypeid) {
            str = str.concat('&','membertypeid=',this.membertypeid.toString())
        }
        if (this.countryid) {
            str = str.concat('&','countryid=',this.countryid.toString())
        }
        if (this.email1  && this.email1 !== YesNoAny.ANY) {
            str = str.concat('&','email1=',this.email1)
        }
        if (this.postonhold && this.postonhold !== YesNoAny.ANY) {
            str = str.concat('&','postonhold=',this.postonhold.toString())
        }
        if (this.address) {
            str = str.concat('&','address=',this.address)
        }
        if (this.paymentmethodID) {
            str = str.concat('&','paymentmethod=',this.paymentmethodID.toString())
        }
        if (this.bankaccountID) {
            str = str.concat('&','paymentmethod=',this.bankaccountID.toString())
        }
        if (this.maxresults) {
            str = str.concat('&','maxresults=',this.maxresults.toString())
        }

        return str;
    }

}