export class Email {
  id: number;
  to: string;
  from: string;
  body: string;
  subject: string;

  constructor(obj?: any) {
    this.id = (obj && obj.id) || null;
    this.to = (obj && obj.to) || null;
    this.from = (obj && obj.from) || null;
    this.body = (obj && obj.body) || null;
    this.subject = (obj && obj.subject) || null;
  }
}
