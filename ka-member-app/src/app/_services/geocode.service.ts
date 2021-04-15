import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import {} from 'googlemaps';

import { Address } from '@app/_models';

@Injectable({ providedIn: 'root' })
export class GeocodeService {
  geocoder!: google.maps.Geocoder;

  constructor() {
    this.geocoder = new google.maps.Geocoder();
  }

  geocode(address: Address): Observable<Address> {
    return new Observable((observer) =>
      this.geocoder.geocode(
        { address: address.toString() },
        (
          results: google.maps.GeocoderResult[],
          status: google.maps.GeocoderStatus
        ) => {
          if ((status = google.maps.GeocoderStatus.OK)) {
            let ll = results[0].geometry.location;
            address.lat = ll.lat();
            address.lng = ll.lng();
          } else {
            console.log(
              'Geocode was not successful: ' +
                status +
                ', idmember=' +
                address.idmember
            );
          }
          observer.next(address);
        }
      )
    );
  }
}
