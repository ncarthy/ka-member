import {
  Component,
  inject,
  ViewChild,
  ElementRef,
  OnInit,
} from '@angular/core';

import { FormBuilder, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { MembersService } from '@app/_services';
import { map, switchMap } from 'rxjs/operators';
import { Address } from '@app/_models';
import { Observable, merge, of } from 'rxjs';

// From https://blog.mapbox.com/fast-geodesic-approximations-with-cheap-ruler-106f229ad016
// Github: https://github.com/mapbox/cheap-ruler
import CheapRuler from 'cheap-ruler'; // Ruler 'points' are lng,lat. Opposite to GMaps
import { ListType } from './list-type.enum';

@Component({
    templateUrl: './map-list.component.html',
    styleUrls: ['./map-list.component.css'],
    imports: [ReactiveFormsModule, RouterLink]
})
export class MapListComponent implements OnInit {
  @ViewChild('mapContainer', { static: false }) gmap!: ElementRef;
  map!: google.maps.Map;
  lat = 51.499063;
  lng = -0.165382;
  mapCentreMarker!: google.maps.Marker;
  loading: boolean = false;
  addresses$!: Observable<Address>;
  markers: [number, google.maps.Marker][] = new Array();
  circle!: google.maps.Circle;
  ruler: CheapRuler = new CheapRuler(51, 'meters'); //51 degrees latitude
  geocoder!: google.maps.Geocoder;
  ids: number[] = new Array();
  form!: FormGroup;

  mapOptions: google.maps.MapOptions = {
    center: new google.maps.LatLng(this.lat, this.lng),
    zoom: 16,
  };

  private membersService = inject(MembersService);
  private formBuilder = inject(FormBuilder);

  constructor() {
    this.geocoder = new google.maps.Geocoder();

    // Create an Observable of Address
    this.addresses$ = this.membersService.getMapList().pipe(
      switchMap((memberAddresses: Address[]) => {
        const obs = memberAddresses.map((x) => {
          return of(x);
        });
        return merge(...obs); // '...' is JS spread syntax
      }),
    );
  }

  private createMarker(address: Address): google.maps.Marker {
    const infoWindow = new google.maps.InfoWindow({
      content: `<p>${address.toString()}</p>`,
    });
    let m: google.maps.Marker = new google.maps.Marker({
      position: new google.maps.LatLng(address.lat, address.lng),
      map: this.map,
    });
    m.addListener('click', () => {
      infoWindow.open(this.map, m);
      setTimeout(() => infoWindow.close(), 3000);
    });
    return m;
  }

  ngOnInit(): void {
    this.form = this.formBuilder.group({
      isEmailList: [false],
      radius: ['200'],
    });
  }

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
      (event: google.maps.MapMouseEvent) => this.drawCircleOnDragend(event),
    );
    this.mapCentreMarker.setMap(this.map);

    this.addCircleToMap(this.lat, this.lng, parseInt(this.f.radius.value));

    let radius = parseInt(this.f.radius.value);
    let iconIN = {
      path: google.maps.SymbolPath.BACKWARD_CLOSED_ARROW,
      strokeColor: 'blue',
      scale: 3,
    };
    let iconOUT = {
      path: google.maps.SymbolPath.BACKWARD_CLOSED_ARROW,
      strokeColor: 'grey',
      scale: 3,
    };
    let ids: number[] = new Array();
    this.addresses$
      .pipe(
        map((address: Address) => {
          let m: google.maps.Marker = this.createMarker(address);
          const pos = m.getPosition();
          if (!pos) return;
          let d = this.ruler.distance(
            [pos.lng(), pos.lat()],
            [this.lng, this.lat],
          );
          if (d <= radius) {
            ids.push(address.idmember);
            m.setIcon(iconIN);
          } else {
            m.setIcon(iconOUT);
          }
          m.setMap(this.map);
          this.markers.push([address.idmember, m]);
        }),
      )
      .subscribe()
      .add(() => {
        this.ids = ids;
      });
  }

  private addCircleToMap(lat: number, lng: number, radius: number) {
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
      radius: radius,
    });
  }

  drawCircleOnDragend(event: google.maps.MapMouseEvent) {
    if (event.latLng) {
      const lat = event.latLng.lat();
      const lng = event.latLng.lng();
      const radius = parseInt(this.f.radius.value);

      this.replaceCircle(lat, lng, radius);

      this.map.setCenter(event.latLng);
    }
  }

  replaceCircle(lat: number, lng: number, radius: number) {
    if (this.circle) {
      this.circle.setMap(null); // remove from map
    }

    this.addCircleToMap(lat, lng, radius);
    this.ids = new Array();

    this.markers.forEach((element) => {
      const pos = element[1].getPosition();

      if (!pos) return;

      const distance = this.ruler.distance([pos.lng(), pos.lat()], [lng, lat]);

      if (distance <= radius) {
        let idmember = element[0];
        if (idmember) {
          this.ids.push(idmember);
          element[1].setIcon({
            path: google.maps.SymbolPath.BACKWARD_CLOSED_ARROW,
            strokeColor: 'blue',
            scale: 3,
          });
        }
      } else if (
        (element[1].getIcon() as google.maps.Symbol).strokeColor != 'grey'
      ) {
        element[1].setIcon({
          path: google.maps.SymbolPath.BACKWARD_CLOSED_ARROW,
          strokeColor: 'grey',
          scale: 3,
        });
      }
    });
  }

  onRadiusChange(radius: number | string) {
    let centre: google.maps.LatLng = this.circle.getCenter()!;
    if (centre) {
      this.replaceCircle(
        centre.lat(),
        centre.lng(),
        parseInt(radius.toString()),
      );
    }
  }

  onIdSelected(idmember: number) {
    this.markers.forEach((element) => {
      let id = element[0];
      if (id && id == idmember) {
        google.maps.event.trigger(element[1], 'click');
      }
    });
  }

  // Required so that the template can access the EnumS
  // From https://stackoverflow.com/a/59289208
  public get ListType() {
    return ListType;
  }
  // convenience getters for easy access to form fields
  get f() {
    return this.form.controls;
  }
}
