import {DateFilterType} from './date-filter.enum';

export class DateRangeFilter {
    startDate: string;
    endDate: string;
    dateType: DateFilterType;

    constructor(obj?: any) {

        this.startDate = obj && obj.startDate || null;
        this.endDate = obj && obj.endDate || null;
        this.dateType = obj && obj.dateType || null;
    }
}