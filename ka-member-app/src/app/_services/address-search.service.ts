import { Injectable, Inject } from '@angular/core';
import {
  HttpClient,
  HttpRequest,
  HttpHeaders
} from '@angular/common/http';

import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { Address } from '@app/_models';

export const ADDRESS_API_KEY =
  'E4dFXtAdQEamUYm9rcA_4A30597';
export const ADDRESS_API_URL =
  'https://api.getAddress.io/find/';

/**
 * AddressSearchService connects to the GetAddress API
 * See: * https://getaddress.io/Documentation
 * 
 * Another provider is https://ideal-postcodes.co.uk/
 */
@Injectable()
export class AddressSearchService {
  constructor(
    private http: HttpClient,
    @Inject(ADDRESS_API_KEY) private apiKey: string,
    @Inject(ADDRESS_API_URL) private apiUrl: string
  ) {}

  search(postcode: string): Observable<Address[]> {

    var trimmed_postcode = postcode.replace(/\s/g, "");

    const params: string = [
      `api-key=${this.apiKey}`,
      `expand=false`,
      `format=true`
    ].join('&');

    const queryUrl = `${this.apiUrl}${trimmed_postcode}?${params}`;

    return this.http.get(queryUrl).pipe(
        map((response : any) => {

          return <Address[]>response['addresses'].map((item : string[]) => {
            console.log("raw item", item); // uncomment if you want to debug
            return new Address(item);
        });
    }));
  }
}
