import {YesNoAny} from '@app/_models/yes-no.enum';

/**
 * MemberFilter is the definition of 
 */
 export class MemberFilter {

    removed: YesNoAny = YesNoAny.NO;

    surname?: string;
    notsurname?: string;
    businessname?: string;
    businessorsurname?: string;
    membertypeid?: number;
    email1?: string;
    postonhold?: YesNoAny;
    addressfirstline?: string;
    paymentmethod?: any;
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
        if (this.email1) {
            str = str.concat('&','email1=',this.email1)
        }
        if (this.postonhold) {
            str = str.concat('&','postonhold=',this.postonhold.toString())
        }
        if (this.addressfirstline) {
            str = str.concat('&','addressfirstline=',this.addressfirstline)
        }
        if (this.paymentmethod) {
            str = str.concat('&','paymentmethod=',this.paymentmethod)
        }

        return str;
    }

}