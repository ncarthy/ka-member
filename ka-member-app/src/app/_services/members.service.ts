import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { MemberCountResponse, MemberSearchResult } from '@app/_models';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';

const baseUrl = `${environment.apiUrl}/members`;

@Injectable({ providedIn: 'root' })
export class MembersService {
    constructor(private http: HttpClient) { }

    getSummary() {
        return this.http.get<MemberCountResponse>(`${baseUrl}/summary`);
    }

    getMailingList() {
        return this.http.get<any>(`${baseUrl}/mailinglist`);
    }

    getEmailList() {
        return this.http.get<any>(`${baseUrl}/emaillist`);
    }

    getLapsed(months: number): Observable<MemberSearchResult[]> {
        const queryUrl = `${baseUrl}/lapsed?months=${months}`;
        
        return this.http.get(queryUrl).pipe(
          map((response: any) => {
            return <any>response['records'].map((item: any) => {
              return new MemberSearchResult(item);
            });
          })
        );
      }
}