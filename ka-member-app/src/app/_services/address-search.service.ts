import { Injectable, Inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '@environments/environment';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { GetAddressIOAddress } from '@app/_models';

/**
 * AddressSearchService connects to the GetAddress API
 * See: * https://getaddress.io/Documentation
 *
 * Another provider is https://ideal-postcodes.co.uk/
 */
@Injectable({ providedIn: 'root' })
export class AddressSearchService {
  constructor(
    private http: HttpClient,
    @Inject(environment.getaddressio_apikey) private apiKey: string,
    @Inject(environment.getaddressio_apiurl) private apiUrl: string,
  ) {}

  search(postcode: string): Observable<GetAddressIOAddress[]> {
    var trimmed_postcode = postcode.replace(/\s/g, '');

    const params: string = [
      `api-key=${this.apiKey}`,
      `expand=false`,
      `format=true`,
    ].join('&');

    const queryUrl = `${this.apiUrl}${trimmed_postcode}?${params}`;

    const addresses$: Observable<GetAddressIOAddress[]> = this.http
      .get(queryUrl)
      .pipe(
        map((response: any) => {
          return <GetAddressIOAddress[]>response['addresses'].map(
            (item: string[]) => {
              //console.log("raw item", item); // uncomment if you want to debug

              // Uses Partial<> to initialize object
              // See https://stackoverflow.com/a/37682352/6941165
              return new GetAddressIOAddress({
                line1: item[0],
                line2: item[1],
                line3: item[2],
                town: item[3],
                county: item[4],
                postcode: this.restoreSpace(postcode.toUpperCase()),
                country: { id: 186, name: 'United Kingdom' },
              });
            },
          );
        }),
      );

    return addresses$;
  }

  private restoreSpace(postcode: string): string {
    if (!postcode.includes(' ')) {
      const index = postcode.length - 3;
      return postcode.slice(0, index) + ' ' + postcode.slice(index);
    }
    return postcode;
  }
}
