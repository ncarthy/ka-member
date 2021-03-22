import { Injectable, Inject } from '@angular/core';
import { HttpClient, HttpRequest, HttpHeaders } from '@angular/common/http';

import { Observable } from 'rxjs';
import { map } from 'rxjs/operators'; // Need to import map https://stackoverflow.com/a/50218001/6941165
import { MemberSearchResult, YesNoAny, MemberFilter } from '@app/_models';

import { environment } from '@environments/environment';

//The @Injectable annotation allows us to inject things into this classes constructor.
@Injectable()
export class MemberSearchService {
  constructor(private http: HttpClient) {}

  /**
   * search
   */
  search(
    query: string,
    removed: YesNoAny = YesNoAny.NO
  ): Observable<MemberSearchResult[]> {
    const params: string = [
      `businessorsurname=${query}`,
      `removed=${removed}`,
    ].join('&');

    const queryUrl = `${environment.apiUrl}/members/filter?${params}`;

    // Add pipe command from https://stackoverflow.com/a/50218001/6941165
    return this.http.get(queryUrl).pipe(
      map((response: any) => {
        // The <any>response means we are telling TypeScript that we’re not
        // interested in doing strict type checking.
        return <any>response['records'].map((item: any) => {
          return new MemberSearchResult(item);
        });
      })
    );
  }

  filter(filter: MemberFilter): Observable<MemberSearchResult[]> {

    const queryUrl = `${environment.apiUrl}/members/filter?${filter}`;

    return this.http.get(queryUrl).pipe(
      map((response: any) => {
        return <any>response['records'].map((item: any) => {
          return new MemberSearchResult(item);
        });
      })
    );
  }

  getAll(): Observable<MemberSearchResult[]> {
    return this.search('', YesNoAny.ANY);
  }
}
