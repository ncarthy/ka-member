export class TransactionDetail {
  id: number;
  date: string | null;
  paymenttypeID: number;
  amount: number;
  bankID: number;
  note: string;
  name: string;
  businessname: string;

  constructor(obj?: any) {
    this.id = (obj && obj.idtransaction) || null;
    this.name = (obj && obj.name) || null;
    this.note = (obj && obj.note) || null;
    this.businessname = (obj && obj.businessname) || null;
    this.amount = (obj && obj.amount) || null;
    this.paymenttypeID = (obj && obj.paymenttypeID) || null;
    this.bankID = (obj && obj.bankID) || null;
    this.date =
      (obj && obj.date && this.convertDateToUKLocale(obj.date)) || null;
  }

  convertDateToUKLocale(m: string): string {
    const t: string[] = m.split(/[- :]/);
    const d = new Date(Date.UTC(+t[0], +t[1] - 1, +t[2]));
    return d.toLocaleDateString('en-GB');
  }
}
