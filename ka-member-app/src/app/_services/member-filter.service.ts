import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { MemberSearchResult, MemberFilter } from '@app/_models';

import { environment } from '@environments/environment';

@Injectable()
export class MemberFilterService {
  constructor(private http: HttpClient) {}

  filter(urlParameters: string): Observable<MemberSearchResult[]> {
    const queryUrl = `${environment.apiUrl}/members/filter?${urlParameters}`;

    return this.http.get(queryUrl).pipe(
      map((response: any) => {
        return <any>response['records'].map((item: any) => {
          return new MemberSearchResult(item);
        });
      }),
    );
  }
}
