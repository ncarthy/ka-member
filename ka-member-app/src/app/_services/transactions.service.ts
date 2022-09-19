import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';

const baseUrl = `${environment.apiUrl}/transactions`;

@Injectable({ providedIn: 'root' })
export class TransactionsService {
  constructor(private http: HttpClient) {}

  getSummary(start: string = '', end: string = '', bankID: string = '') {
    if (bankID == null) {
      bankID = '';
    }

    return this.http.get<any>(
      `${baseUrl}/summary?start=${start}&end=${end}&bankID=${bankID}`
    );
  }

  getDetail(month: string = '', year: string = '', bankID: string = '') {
    return this.http.get<any>(
      `${baseUrl}/detail?month=${month}&year=${year}&bankID=${bankID}`
    );
  }
}
