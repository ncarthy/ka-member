import { Injectable, Inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '@environments/environment';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { Address } from '@app/_models';

/**
 * AddressSearchService connects to the GetAddress API
 * See: * https://getaddress.io/Documentation
 *
 * Another provider is https://ideal-postcodes.co.uk/
 */
@Injectable({ providedIn: 'root' })
export class AddressSearchService {

  private apiKey: string = environment.getaddressio_apikey;
  private apiUrl: string = environment.getaddressio_apiurl;

  constructor(
    private http: HttpClient,
  ) {}

  search(postcode: string): Observable<Address[]> {
    //var trimmed_postcode = postcode.replace(/\s/g, '');

    const params: string = `api_key=${this.apiKey}`;

    const queryUrl = `${this.apiUrl}${postcode}?${params}`;

    const addresses$: Observable<any[]> = this.http
      .get(queryUrl)
      .pipe(
        map((response: any) => {
          return <any[]>response['result'].map(
            (address:any) => {
              // Uses Partial<> to initialize object
              // See https://stackoverflow.com/a/37682352/6941165
              return new Address({
                addressfirstline: address.line_1 ?? '',
                addresssecondline: address.line_2 ?? '',                
                city: address.post_town ?? '',
                county: address.county ?? '',
                postcode: address.postcode,
                country: 186, // hardcoded to UK for now
                lat: address.latitude,
                lng: address.longitude,
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
