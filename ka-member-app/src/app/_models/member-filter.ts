import {YesNoAny} from './yes-no.enum';
import {DateRangeFilter} from './date-range-filter';
import {DateFilterType} from './date-filter.enum';

/**
 * MemberFilter is the definition of 
 */
 export class MemberFilter {

    removed?: YesNoAny;

    surname?: string;
    notsurname?: string;
    businessname?: string;
    businessorsurname?: string;
    membertypeid?: number;
    countryid?: number;
    email1?: YesNoAny;
    postonhold?: YesNoAny;
    address?: string;
    paymenttypeid?: number;
    bankaccountid?: number;
    maxresults?: number;
    dateranges?: DateRangeFilter[];

    constructor(obj?: any) {

        this.removed = obj && obj.removed || null;
        this.surname = obj && obj.surname || null;
        this.notsurname = obj && obj.notsurname || null;
        this.businessname = obj && obj.businessname || null;
        this.businessorsurname = obj && obj.businessorsurname || null;
        this.membertypeid = obj && obj.membertypeid || null;
        this.countryid = obj && obj.countryid || null;
        this.email1 = obj && obj.email1 || null;
        this.postonhold = obj && obj.postonhold || null;
        this.address = obj && obj.address || null;
        this.paymenttypeid = obj && obj.paymenttypeid || null;
        this.bankaccountid = obj && obj.bankaccountid || null;
        this.maxresults = obj && obj.maxresults || null;
        this.dateranges = obj && obj.dateranges || null;
    }

    /* overload toString */
    /* From https://stackoverflow.com/a/35361695/6941165 */
    public toString = () : string => {

        var str = `removed=${this.removed!}`;
        
        if (this.surname) {
            str = str.concat('&','surname=',this.surname);
        }
        if (this.notsurname) {
            str = str.concat('&','notsurname=',this.notsurname);
        }
        if (this.businessname) {
            str = str.concat('&','businessname=',this.businessname);
        }
        if (this.businessorsurname) {
            str = str.concat('&','businessorsurname=',this.businessorsurname);
        }
        if (this.membertypeid) {
            str = str.concat('&','membertypeid=',this.membertypeid.toString());
        }
        if (this.countryid) {
            str = str.concat('&','countryid=',this.countryid.toString());
        }
        if (this.email1  && this.email1 !== YesNoAny.ANY) {
            str = str.concat('&','email1=',this.email1);
        }
        if (this.postonhold && this.postonhold !== YesNoAny.ANY) {
            str = str.concat('&','postonhold=',this.postonhold.toString());
        }
        if (this.address) {
            str = str.concat('&','address=',this.address)
        }
        if (this.paymenttypeid) {
            str = str.concat('&','paymenttypeid=',this.paymenttypeid.toString());
        }
        if (this.bankaccountid) {
            str = str.concat('&','bankid=',this.bankaccountid.toString());
        }
        if (this.maxresults) {
            str = str.concat('&','maxresults=',this.maxresults.toString());
        }     
        
        if (this.dateranges) {            
            this.dateranges.forEach( (dateRangeFilter : DateRangeFilter) => {
                if (dateRangeFilter.dateType) {
                    if (dateRangeFilter.startDate) {
                        str = str.concat('&',dateRangeFilter.dateType,'start=',dateRangeFilter.startDate);
                    }
                    if (dateRangeFilter.endDate) {
                        str = str.concat('&',dateRangeFilter.dateType,'end=',dateRangeFilter.endDate);
                    }
                }
            });
        }

        return str;
    }

}