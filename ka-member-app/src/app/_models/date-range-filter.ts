import {DateFilterType} from './date-filter.enum';

export class DateRangeFilter {
    start: string;
    end: string;
    type: DateFilterType;

    constructor(obj?: any) {

        this.start = obj && obj.start || null;
        this.end = obj && obj.end || null;
        this.type = obj && obj.type || null;
    }
}