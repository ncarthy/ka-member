import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { environment } from '@environments/environment';
import { MemberCountResponse, MemberSearchResult } from '@app/_models';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';

const baseUrl = `${environment.apiUrl}/members`;

@Injectable({ providedIn: 'root' })
export class MembersService {
  constructor(private http: HttpClient) {}

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

  getPayingHonlife(): Observable<MemberSearchResult[]> {
    return this.http.get(`${baseUrl}/payinghonlife`).pipe(
      map((response: any) => {
        return <any>response['records'].map((item: any) => {
          return new MemberSearchResult(item);
        });
      })
    );
  }

  getContributingExMembers(): Observable<MemberSearchResult[]> {
    return this.http.get(`${baseUrl}/cem`).pipe(
      map((response: any) => {
        return <any>response['records'].map((item: any) => {
          return new MemberSearchResult(item);
        });
      })
    );
  }

  getDiscountMembers(): Observable<MemberSearchResult[]> {
    return this.http.get(`${baseUrl}/discount`).pipe(
      map((response: any) => {
        return <any>response['records'].map((item: any) => {
          return new MemberSearchResult(item);
        });
      })
    );
  }

  getMemberPayingTwice(): Observable<MemberSearchResult[]> {
    return this.http.get(`${baseUrl}/duplicatepayers`).pipe(
      map((response: any) => {
        return <any>response['records'].map((item: any) => {
          return new MemberSearchResult(item);
        });
      })
    );
  }

  getMemberWithoutUKAddress(): Observable<MemberSearchResult[]> {
    return this.http.get(`${baseUrl}/noukaddress`).pipe(
      map((response: any) => {
        return <any>response['records'].map((item: any) => {
          return new MemberSearchResult(item);
        });
      })
    );
  }

  getInvalidEmails(): Observable<MemberSearchResult[]> {
    return this.http.get(`${baseUrl}/invalidemails`).pipe(
      map((response: any) => {
        return <any>response['records'].map((item: any) => {
          return new MemberSearchResult(item);
        });
      })
    );
  }
}
