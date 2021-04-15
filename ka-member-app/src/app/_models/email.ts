export class Email {
    id: string;
    to: string;    
    from: string;
    body: string;

    constructor(obj?: any) {

        this.id = obj && obj.id || null;
        this.to = obj && obj.to || null;
        this.from = obj && obj.from || null;
        this.body = obj && obj.body || null;
    }
}