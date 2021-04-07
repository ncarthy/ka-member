import {
  Component,
  AfterViewInit,
  ViewChild,
  ElementRef,
  OnInit,
} from '@angular/core';
import {} from 'googlemaps';
import { MemberService, MembersService } from '@app/_services';
import { concatMap, delay, map, switchMap } from 'rxjs/operators';
import { Address, MapMarker, MemberSearchResult } from '@app/_models';
import { Observable, from, merge, of, concat, bindCallback } from 'rxjs';
import CheapRuler from 'cheap-ruler';

@Component({
  templateUrl: './map-list.component.html',
})
export class MapListComponent implements OnInit {
  @ViewChild('mapContainer', { static: false }) gmap!: ElementRef;
  map!: google.maps.Map;
  lat = 51.499063;
  lng = -0.165382;
  mapCentreMarker!: google.maps.Marker;
  loading: boolean = false;
  addresses$!: Observable<Address>;
  markers: google.maps.Marker[] = new Array();
  circle!: google.maps.Circle;
  radius: number = 200;
  ruler: CheapRuler = new CheapRuler(this.lat, 'meters');
  postcodesInCircle: string[] = new Array();
  geocoder!: google.maps.Geocoder;
  ids: number[] = new Array();

  mapOptions: google.maps.MapOptions = {
    center: new google.maps.LatLng(this.lat, this.lng),
    zoom: 16,
  };

  constructor(
    private membersService: MembersService,
    private memberService: MemberService
  ) {
    this.geocoder = new google.maps.Geocoder();

    // Create an Observable of Address
    this.addresses$ = this.membersService.getMapList().pipe(
      switchMap((memberAddresses: Address[]) => {
        const obs = memberAddresses.map((x) => {
          return of(x);
        });
        return merge(...obs);
      })
    );
  }

  private createMarker(address: Address): google.maps.Marker {
    const infoWindow = new google.maps.InfoWindow({
      content: `<p>${address.toString()}</p>`,
    });
    let m: google.maps.Marker = new google.maps.Marker({
      position: new google.maps.LatLng(address.lat, address.lng),
      map: this.map,
      icon: {
        path: google.maps.SymbolPath.BACKWARD_CLOSED_ARROW,
        strokeColor: 'blue',
        scale: 3,
      },
      label: address.idmember.toString(),
    });
    m.addListener('click', () => {
      infoWindow.open(this.map, m);
      setTimeout(() => infoWindow.close(), 3000);
    });
    return m;
  }

  ngOnInit(): void {}

  ngAfterViewInit() {
    this.mapInitializer();
  }

  mapInitializer() {
    this.map = new google.maps.Map(this.gmap.nativeElement, this.mapOptions);
    this.mapCentreMarker = new google.maps.Marker({
      position: new google.maps.LatLng(this.lat, this.lng),
      map: this.map,
      draggable: true,
    });
    google.maps.event.addListener(
      this.mapCentreMarker,
      'dragend',
      (event: google.maps.MapMouseEvent) => this.drawCircleOnDragend(event)
    );
    this.mapCentreMarker.setMap(this.map);

    this.addCircleToMap(this.lat, this.lng);

    this.addresses$.pipe(
      map((address: Address) => {
        let m: google.maps.Marker = this.createMarker(address);
        const pos = m.getPosition();
        if (!pos) return;
        let d = this.ruler.distance([pos.lat(), pos.lng()], [this.lat, this.lng]);      
        if (d <=
            this.radius
        ) {
          this.ids.push(address.idmember);
        }
        m.setMap(this.map);
        this.markers.push(m);
      })
    ).subscribe();

    /* let i = 1;
    this.addresses$
      .pipe(
        map((address: Address) => {
          if (!address.lat || !address.lng) {
            this.geocoder.geocode(
            { address: address.toString() },
            (results, status) => {
              if (status == 'OK') {
                let m = this.memberService.setPrimaryGeometry(
                  address.idmember,
                  results[0].geometry.location.lat(),
                  results[0].geometry.location.lng()
                ).subscribe(() => console.log('Lat/Lng updated for idmember='+address.idmember));
                address.lat = results[0].geometry.location.lat();
                address.lng = results[0].geometry.location.lng();
              } else {
                console.log('Geocode was not successful: ' + status+', idmember='+address.idmember);
              }                      
            return address;
            }
          );
          } else {
            return address;
          }
        })
      )
      .subscribe();*/

    /*this.markers$.subscribe((m: google.maps.Marker) => {
      const pos = m.getPosition();
      if (!pos) return;
      let d = this.ruler.distance([pos.lat(), pos.lng()], [this.lat, this.lng]);      
      if (d <=
          this.radius
      ) {
        m.setOpacity(1);
      } else {
        m.setOpacity(0.25);
      }
      m.setMap(this.map);
      this.markers.push(m);
    });*/
/*
    this.addresses$
      .pipe(
        concatMap((value: Address, index: number) => {
          console.log(value.toString());
          return this.geocode(value);
        }),
        concatMap((value: Address, index: number) => {
          return this.memberService.setSecondaryGeometry(
            value.idmember,
            value.lat,
            value.lng
          );
        })
      )
      .subscribe((reasult) => console.log('done'));*/
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

  addCircleToMap(lat: number, lng: number) {
    // add new circle
    this.circle = new google.maps.Circle({
      strokeColor: '#FF0000',
      strokeOpacity: 0.8,
      strokeWeight: 2,
      fillColor: '#FF0000',
      fillOpacity: 0.35,
      map: this.map,
      center: {
        lat: lat,
        lng: lng,
      },
      radius: this.radius,
    });
  }

  drawCircleOnDragend(event: google.maps.MapMouseEvent) {
    if (this.circle) {
      this.circle.setMap(null); // remove from map
    }

    const lat = event.latLng.lat();
    const lng = event.latLng.lng();

    this.addCircleToMap(lat, lng);
    this.ids = new Array();

    this.markers.forEach((element) => {
      const pos = element.getPosition();

      if (!pos) return;

      const distance = this.ruler.distance([pos.lat(), pos.lng()], [lat, lng]);

      if (distance <= this.radius) {
        let label = element.getLabel();
        if (label) {
          let text = label.text;
          this.ids.push(parseInt(text));
        }        
      }
    });

    this.map.setCenter(event.latLng);
  }
}
