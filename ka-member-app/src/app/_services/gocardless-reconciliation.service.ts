import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { environment } from '@environments/environment';

const baseUrl = `${environment.apiUrl}/gocardless`;

@Injectable({ providedIn: 'root' })
export class GoCardlessReconciliationService {
  constructor(private http: HttpClient) {}

  getSummary(period: 'week' | 'month' = 'week') {
    const params = new HttpParams().set('period', period);
    return this.http.get<any>(`${baseUrl}/reconciliation`, { params });
  }
}
