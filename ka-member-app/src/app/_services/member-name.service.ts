import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { merge, Observable, of } from 'rxjs';
import { concatMap, map, reduce, switchMap } from 'rxjs/operators';

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
        return merge(...obs);
      })
    );
  }

  /** Generate Observable of strings which are the concatanation of honorific, name and surname */
  getNamesStringForMember(idmember: number) {
    const http$ = this.http.get<MemberName[]>(`${baseUrl}/${idmember}`); //Observable<MemberName[]>
    /*http$.pipe(map((mn:MemberName) => return this.concatName(mn)),reduce((prevValue, currValue, idx) => 
      {return idx == 0 ? currVal : prevVal + ' AND ' + currVal;}));*/

    /*
      http$
      .pipe(
        switchMap(data => data as MemberName[]), 
    
        reduce((finalString: string, value: MemberName, idx: number) => {
            const name = this.concatName(value);
            return idx == 0 ? value : finalString + ' and ' + name;

          })
      )*/
/*
    const names = [
      new MemberName({ honorific: 'Mr', firstname: 'Neil', surname: 'Carthy' }),
      new MemberName({
        honorific: 'Mrs',
        firstname: 'Selina',
        surname: 'Carthy',
      }),      
    ];
    const names$ = of(names);*/
    const x$ = http$.pipe(switchMap((names: MemberName[]) => {
      const obs = names.map((x) => {
        return of(this.concatName(x));
      });
      return merge(...obs);
    }));
    return x$.pipe(reduce((finalString: string, value: string, idx: number) => {
      return idx == 0 ? value : finalString + ' and ' + value;

    }))
  }

  concatName(x: MemberName): string {
    return (x.honorific ? x.honorific + ' ' : '').concat(
      x.firstname ? x.firstname + ' ' : '',
      x.surname
    );
  }

  /*
groupIds$.pipe(
    switchMap(groups => {
        const observables = groups.map(id => {
            // your function that returns the observable
            return something.getObservable(id);
        });
        return merge(...observables);
    })

    */
}
