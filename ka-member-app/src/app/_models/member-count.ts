export class MemberCount {
  id: number;
  name: string;
  count: number;
  multiplier: number;
  actmultiplier: number;
  contribution: number;

  constructor(obj?: any) {
    this.id = (obj && obj.id) || null;
    this.name = (obj && obj.name) || null;
    this.count = (obj && obj.count) || 0;
    this.multiplier = (obj && obj.multiplier) || 0;
    this.actmultiplier = (obj && obj.actmultiplier) || 0;
    this.contribution = (obj && obj.contribution) || 0;
  }
}

export class MemberCountResponse {
  total: number;
  count: number;
  records: MemberCount[];

  constructor(obj?: any) {
    this.total = (obj && obj.total) || 0;
    this.count = (obj && obj.count) || 0;
    this.records = (obj && obj.records) || null;
  }
}
