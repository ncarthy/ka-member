export class MapMarker {
  postcode: string;
  gpslat: number;
  gpslng: number;
  count: number;

  constructor(obj?: any) {
    this.postcode = (obj && obj.postcode) || null;
    this.gpslat = (obj && obj.gpslat) || null;
    this.count = (obj && obj.count) || null;
    this.gpslng = (obj && obj.gpslng) || null;
  }
}
