import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { environment } from '@environments/environment';

const baseUrl = `${environment.apiUrl}/gocardless`;

@Injectable({ providedIn: 'root' })
export class GoCardlessReconciliationService {
  constructor(private http: HttpClient) {}

  getSummary(
    period: 'week' | 'month' = 'week',
    startDate: string = '',
    endDate: string = '',
    dateRange: string = '',
  ) {
    let params = new HttpParams();
    if (startDate && endDate) {
      params = params.set('startDate', startDate).set('endDate', endDate);
      if (dateRange) {
        params = params.set('dateRange', dateRange);
      }
    } else {
      params = params.set('period', period);
    }
    return this.http.get<any>(`${baseUrl}/reconciliation`, { params });
  }
}
