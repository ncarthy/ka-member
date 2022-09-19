import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '@environments/environment';
import {
  Address,
  MemberCountResponse,
  MemberInvalidEmail,
  MemberInvalidPostcode,
  MemberSearchResult,
} from '@app/_models';
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

  getNoEmailList() {
    return this.http.get<any>(`${baseUrl}/noemaillist`);
  }

  getMapList(): Observable<Address[]> {
    const queryUrl = `${baseUrl}/maplist`;

    return this.http.get(queryUrl).pipe(
      map((response: any) => {
        return <any>response['records'].map((item: any) => {
          return new Address(item);
        });
      })
    );
  }

  getLapsed(months: number): Observable<MemberSearchResult[]> {
    const queryUrl = `${baseUrl}/lapsed/${months}`;

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

  getInvalidEmails(): Observable<MemberInvalidEmail[]> {
    return this.http.get(`${baseUrl}/invalidemails`).pipe(
      map((response: any) => {
        return <any>response['records'].map((item: any) => {
          return new MemberInvalidEmail(item);
        });
      })
    );
  }

  getInvalidPostcodes(): Observable<MemberInvalidPostcode[]> {
    return this.http.get(`${baseUrl}/invalidpostcodes`).pipe(
      map((response: any) => {
        return <any>response['records'].map((item: any) => {
          return new MemberInvalidPostcode(item);
        });
      })
    );
  }

  getDeletedButNotFormer(): Observable<MemberSearchResult[]> {
    return this.http.get(`${baseUrl}/deletedbutnotformer`).pipe(
      map((response: any) => {
        return <any>response['records'].map((item: any) => {
          return new MemberSearchResult(item);
        });
      })
    );
  }

  getLapsedCEMs(months: number): Observable<MemberSearchResult[]> {
    return this.http.get(`${baseUrl}/lapsedcem/${months}`).pipe(
      map((response: any) => {
        return <any>response['records'].map((item: any) => {
          return new MemberSearchResult(item);
        });
      })
    );
  }

  setLapsedCEMsToFormer(months: number) {
    return this.http.patch(
      `${baseUrl}/lapsedcem/${months}`,
      `{"method": "setToFormer"}`
    );
  }

  getFormerMembersWithRecentPayment(
    months: number
  ): Observable<MemberSearchResult[]> {
    return this.http.get(`${baseUrl}/formermember/${months}`).pipe(
      map((response: any) => {
        return <any>response['records'].map((item: any) => {
          return new MemberSearchResult(item);
        });
      })
    );
  }

  getOldFormerMembers(months: number): Observable<MemberSearchResult[]> {
    return this.http.get(`${baseUrl}/oldformermember/${months}`).pipe(
      map((response: any) => {
        return <any>response['records'].map((item: any) => {
          return new MemberSearchResult(item);
        });
      })
    );
  }

  anonymizeOldFormerMembers(months: number) {
    return this.http.patch(
      `${baseUrl}/oldformermember/${months}`,
      `{"method": "Anonymize"}`
    );
  }
}
