import {
  Component,
  inject,
  ViewChild,
  ElementRef,
  OnInit,
} from '@angular/core';
import { JsonPipe } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { MembersService } from '@app/_services';
import { map, switchMap } from 'rxjs/operators';
import { Address } from '@app/_models';
import { Observable, merge, of } from 'rxjs';
import { MailingListComponent } from '../mailing-list/mailing-list.component';
import { EmailListComponent } from '../email-list/email-list.component';
import { fromArrayToElement } from '@app/_helpers';

// From https://blog.mapbox.com/fast-geodesic-approximations-with-cheap-ruler-106f229ad016
// Github: https://github.com/mapbox/cheap-ruler
import CheapRuler from 'cheap-ruler'; // Ruler 'points' are lng,lat. Opposite to GMaps
import { ListType } from './list-type.enum';

@Component({
  templateUrl: './map-list.component.html',
  styleUrls: ['./map-list.component.css'],
  imports: [
    ReactiveFormsModule,
    RouterLink,
    JsonPipe,
    MailingListComponent,
    EmailListComponent,
  ],
})
export class MapListComponent implements OnInit {
  @ViewChild('mapContainer', { static: false }) gmap!: ElementRef;
  map!: google.maps.Map;
  lat = 51.499063;
  lng = -0.165382;
  mapCentreMarker!: google.maps.marker.AdvancedMarkerElement;
  loading: boolean = false;
  addresses$!: Observable<Address>;
  markers: [number, google.maps.marker.AdvancedMarkerElement][] = new Array();
  circle!: google.maps.Circle;
  ruler: CheapRuler = new CheapRuler(51, 'meters'); //51 degrees latitude
  geocoder!: google.maps.Geocoder;
  ids: number[] = new Array();
  form!: FormGroup;

  mapOptions: google.maps.MapOptions = {
    center: new google.maps.LatLng(this.lat, this.lng),
    zoom: 16,
    mapId: '879f7bdf49a5142f6e525637',
  };

  private membersService = inject(MembersService);
  private formBuilder = inject(FormBuilder);

  constructor() {
    this.geocoder = new google.maps.Geocoder();

    // Create an Observable of Address
    this.addresses$ = this.membersService.getMapList().pipe(      
      fromArrayToElement(), // Convert Observable<Address[]> to Observable<Address>
    );
  }

  /**
   * Create a marker for the given address and add to the map
   * @param address 
   * @returns The marker
   */
  private createMarker(
    address: Address,
  ): google.maps.marker.AdvancedMarkerElement {
    
    const infoWindow = new google.maps.InfoWindow({
      content: `<p>${address.toString()}</p>`,
    });

    if (!address.lat || !address.lng) {
      return null as any;
    }
    
    try {
      const x : google.maps.LatLngLiteral = {lat: address.lat, lng: address.lng};
      let m: google.maps.marker.AdvancedMarkerElement =
        new google.maps.marker.AdvancedMarkerElement({
          position: x,
          //position: new google.maps.LatLng(address.lat, address.lng),
          map: this.map,
        });
      m.addListener('click', () => {
        infoWindow.open(this.map, m);
        setTimeout(() => infoWindow.close(), 3000);
      });
      return m;
    } catch (e) {
      console.error('Error creating marker for address: ' + address.toString());
      return null as any;
    }
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

    this.addCircleToMap(this.lat, this.lng, parseInt(this.f['radius'].value));

    let radius = parseInt(this.f['radius'].value);
    
    let ids: number[] = new Array();
    this.addresses$
      .pipe(
        map((address: Address) => {
          let marker: google.maps.marker.AdvancedMarkerElement =
            this.createMarker(address);
          
          if (!marker) return;

          let distance = this.ruler.distance(
            [address.lng, address.lat],
            [this.lng, this.lat],
          );

          if (distance <= radius) {
            ids.push(address.idmember);          
            const icon = document.createElement('div');
            icon.innerHTML = '<i class="fa-solid fa-check"></i>';
            marker.content = new google.maps.marker.PinElement({
              glyph: icon,
              glyphColor: 'black',
              background: 'limegreen',
              borderColor: 'green',
            }).element;
          } else {
            marker.content = new google.maps.marker.PinElement({
              glyph: 'X',
              glyphColor: 'grey',
              background: 'lightgrey',
              borderColor: 'grey',
            }).element;
          }
          marker.map = this.map;
          this.markers.push([address.idmember, marker]);
        }),
      )
      .subscribe()
      .add(() => {
        this.ids = ids;

        const pinScaled = new google.maps.marker.PinElement({
          scale: 2,
        });
        this.mapCentreMarker = new google.maps.marker.AdvancedMarkerElement({
          position: new google.maps.LatLng(this.lat, this.lng),
          map: this.map,
          gmpDraggable: true,
          content: pinScaled.element,
        });
        this.mapCentreMarker.addListener(
          'dragend',
          (event: google.maps.MapMouseEvent) => this.drawCircleOnDragend(event),
        );    
        this.mapCentreMarker.map = this.map;
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
      const radius = parseInt(this.f['radius'].value);

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
      let marker = element[1];
      const pos = marker.position as google.maps.LatLngLiteral;

      if (!pos) return;

      const distance = this.ruler.distance([pos.lng, pos.lat], [lng, lat]);

      if (distance <= radius) {
        let idmember = element[0];
        if (idmember) {
          this.ids.push(idmember);
          const icon = document.createElement('div');
          icon.innerHTML = '<i class="fa-solid fa-check"></i>';
          marker.content = new google.maps.marker.PinElement({
              glyph: icon,
              glyphColor: 'black',
              background: 'limegreen',
              borderColor: 'green',
          }).element;          
        }
      } else {
          marker.content = new google.maps.marker.PinElement({
              glyph: 'X',
              glyphColor: 'grey',
              background: 'lightgrey',
              borderColor: 'lightgrey',
          }).element;  
      }
    });
  }

  onRadiusChange(e: Event) {
    let radius: number | string = (e.target as HTMLInputElement).value;
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
