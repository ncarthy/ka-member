import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { merge, Observable, of } from 'rxjs';
import { reduce, switchMap } from 'rxjs/operators';

import { environment } from '@environments/environment';
import { Member, MemberName } from '@app/_models';

const baseUrl = `${environment.apiUrl}/name/idmember`;

@Injectable({ providedIn: 'root' })
export class MemberNameService {
  constructor(private http: HttpClient) {}

  getAllForMember(idmember: number) {
    return this.http.get<MemberName[]>(`${baseUrl}/${idmember}`);
  }

  updateAllForMember(idmember: number, names: MemberName[]) {
    return this.http.put(`${baseUrl}/${idmember}`, names);
  }

  deleteAllForMember(idmember: number) {
    return this.http.delete(`${baseUrl}/${idmember}`);
  }

  concatAllForMember2(idmember: number): Observable<MemberName[]> {
    return this.http.get<MemberName[]>(`${baseUrl}/${idmember}`);
  }

  /** Generate Observable of strings which are the concatanation of honorific, name and surname */
  getNamesForMember(idmember: number): Observable<string> {
    const http$ = this.http.get<MemberName[]>(`${baseUrl}/${idmember}`);
    return http$.pipe(
      switchMap((names: MemberName[]) => {
        const obs = names.map((x) => {
          return of(this.concatName(x));
        });
        return merge(...obs); // '...' is JS spread syntax
      }),
    );
  }

  /** Generate Observable of strings which are the concatanation of honorific, name and surname */
  getNamesStringForMember(idmember: number): Observable<string> {
    // http$ is of type Observable<MemberName[]>
    const http$ = this.http.get<MemberName[]>(`${baseUrl}/${idmember}`);

    // x$ is of type Observable<string> after the various transforms
    const x$ = http$.pipe(
      switchMap((names: MemberName[]) => {
        const obs = names.map((x) => {
          return of(this.concatName(x));
        });
        return merge(...obs); // '...' is JS spread syntax
      }),
    );
    return x$.pipe(
      reduce((finalString: string, value: string, idx: number) => {
        return idx == 0 ? value : finalString + ' and ' + value;
      }),
    );
  }

  // method extracted for clarity
  concatName(x: MemberName): string {
    return (x.honorific ? x.honorific + ' ' : '').concat(
      x.firstname ? x.firstname + ' ' : '',
      x.surname,
    );
  }
}
