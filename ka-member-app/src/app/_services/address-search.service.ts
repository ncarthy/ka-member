import { Injectable, Inject } from '@angular/core';
import {
  HttpClient,
  HttpRequest,
  HttpHeaders
} from '@angular/common/http';

import { Observable, from } from 'rxjs';
import { map, mergeAll } from 'rxjs/operators';
import { GetAddressAddress } from '@app/_models';

export const ADDRESS_API_KEY =
  'SdcqMcX0jkOi4rMBEMNx3Q30597';
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

  search(postcode: string): Observable<GetAddressAddress[]> {

    var trimmed_postcode = postcode.replace(/\s/g, "");

    const params: string = [
      `api-key=${this.apiKey}`,
      `expand=false`,
      `format=true`
    ].join('&');

    const queryUrl = `${this.apiUrl}${trimmed_postcode}?${params}`;

    const addresses$:Observable<GetAddressAddress[]> = this.http.get(queryUrl).pipe(
      map((response : any) => {
        return <GetAddressAddress[]>response['addresses'].map((item : string[]) => {

          //console.log("raw item", item); // uncomment if you want to debug

          // Uses Partial<> to initialize object
          // See https://stackoverflow.com/a/37682352/6941165
          return new GetAddressAddress({
            line1: item[0],
            line2: item[1],
            line3: item[2],
            town: item[3],
            county: item[4],
            postcode: postcode.toUpperCase(),
            country: {id: 186, name: "United Kingdom"}
          });
        });
      })
    );

    return  addresses$;
  }
}
