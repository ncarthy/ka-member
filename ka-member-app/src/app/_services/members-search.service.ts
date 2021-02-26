import { Injectable, Inject } from '@angular/core';
import {
  HttpClient,
  //HttpRequest,
  //HttpHeaders
} from '@angular/common/http';

import { Observable } from 'rxjs';
import { map } from 'rxjs/operators'; // Need to import map https://stackoverflow.com/a/50218001/6941165
import { MemberSearchResult } from '@app/_models';

import { environment } from '@environments/environment';


 //The @Injectable annotation allows us to inject things into this classes constructor.
@Injectable()
export class MemberSearchService {    

  constructor(
    private http: HttpClient
  ) {}

  /**
  * search
  */
  search(query: string): Observable<MemberSearchResult[]> {

        const queryUrl = `${environment.apiUrl}/members/filter.php`;
        const body = JSON.stringify({
            businessorsurname: query,
            removed: 'no'
        });

        // Add pipe command from https://stackoverflow.com/a/50218001/6941165
        return this.http.post(queryUrl,body).pipe(map(response => {

            // The <any>response means we are telling TypeScript that we’re not 
            // interested in doing strict type checking.
            return <any>response['records'].map(item => {
                console.log("raw item", item); // uncomment if you want to debug
                return new MemberSearchResult({
                    id: item.id,
                    membershiptype: item.type,
                    name: item.name,
                    businessname: item.business,
                    note: item.note,
                    postcode: item.postcode,
                    reminderdate: item.reminderdate,
                    deletedate: item.deletedate,
                    lasttransactiondate: item.lasttransactiondate,
                    email: item.email1
                });
            });
         }));
    }
}