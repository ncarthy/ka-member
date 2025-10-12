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

  static readonly LAT = 51.499063;
  static readonly LNG = -0.165382;
  static readonly MAPID = '879f7bdf49a5142f6e525637'; // from Google Cloud Console

  mapCentreMarker!: google.maps.marker.AdvancedMarkerElement;
  addresses$!: Observable<Address>;
  markers: [number, google.maps.marker.AdvancedMarkerElement][] = new Array();
  circle!: google.maps.Circle;
  ruler: CheapRuler = new CheapRuler(51, 'meters'); //51 degrees latitude
  ids_of_members_inside_circle: number[] = new Array();
  form!: FormGroup;

  mapOptions: google.maps.MapOptions = {
    center: { lat: MapListComponent.LAT, lng: MapListComponent.LNG },
    zoom: 16,
    mapId: MapListComponent.MAPID,
  };

  private membersService = inject(MembersService);
  private formBuilder = inject(FormBuilder);

  constructor() {
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
      const latlng: google.maps.LatLngLiteral = {
        lat: address.lat,
        lng: address.lng,
      };
      let m: google.maps.marker.AdvancedMarkerElement =
        new google.maps.marker.AdvancedMarkerElement({
          position: latlng,
          map: this.map,
        });
      m.addListener('click', () => {
        infoWindow.open(this.map, m);
        setTimeout(() => infoWindow.close(), 3000);
      });
      return m;
    } catch (e) {
      //console.error('Error creating marker for address: ' + address.toString());
      return null as any;
    }
  }

  ngOnInit(): void {
    this.form = this.formBuilder.group({
      isEmailList: [false], // default to mailing list, not email list
      radius: ['200'], // default radius in metres
    });
  }

  ngAfterViewInit() {
    this.mapInitializer();
  }

  mapInitializer() {
    this.map = new google.maps.Map(this.gmap.nativeElement, this.mapOptions);

    this.addCircleToMap(
      MapListComponent.LAT,
      MapListComponent.LNG,
      parseInt(this.f['radius'].value),
    );

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
            [MapListComponent.LNG, MapListComponent.LAT],
          );

          if (distance <= radius) {
            ids.push(address.idmember);
            marker.content = this.contentOfInsideMarker();
          } else {
            marker.content = this.contentOfOutsideMarker();
          }
          marker.map = this.map;
          this.markers.push([address.idmember, marker]);
        }),
      )
      .subscribe()
      .add(() => {
        this.ids_of_members_inside_circle = ids;

        const pinScaled = new google.maps.marker.PinElement({
          scale: 1.5,
        });
        this.mapCentreMarker = new google.maps.marker.AdvancedMarkerElement({
          position: new google.maps.LatLng(
            MapListComponent.LNG,
            MapListComponent.LAT,
          ),
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

  /**
   * Add a circle to the map
   * @param lat Latitude of centre of new circle
   * @param lng Longitude of centre of new circle
   * @param radius Radius of new circle
   */
  addCircleToMap(lat: number, lng: number, radius: number) {
    
    this.circle = new google.maps.Circle({
      strokeColor: 'red',
      strokeOpacity: 0.8,
      strokeWeight: 2,
      fillColor: 'red',
      fillOpacity: 0.35,
      map: this.map,
      center: {
        lat: lat,
        lng: lng,
      },
      radius: radius,
    });
  }

  /**
   * Called when the user drags the centre marker to a new position.
   * It draws a new circle at the new position and updates the list of members inside the circle
   * @param event 
   */
  drawCircleOnDragend(event: google.maps.MapMouseEvent) {
    if (event.latLng) {
      const lat = event.latLng.lat();
      const lng = event.latLng.lng();
      const radius = parseInt(this.f['radius'].value);

      this.replaceCircle(lat, lng, radius);

      this.map.setCenter(event.latLng);
    }
  }

  /**
   * Draw a new circle on the map, removing any previous circle. Also add all address markers and
   * record those members that are inside the circle.
   * @param lat Latitude of centre of new circle
   * @param lng Longitude of centre of new circle
   * @param radius Radius of new circle
   */
  replaceCircle(lat: number, lng: number, radius: number) {
    if (this.circle) {
      this.circle.setMap(null); // remove from map
    }

    this.addCircleToMap(lat, lng, radius);

    // initialize the array again, clearing previous contents
    this.ids_of_members_inside_circle = new Array();

    // Draw the markers again, changing their content depending on whether they are inside or outside the circle
    this.markers.forEach((element) => {
      let marker = element[1];
      const pos = marker.position as google.maps.LatLngLiteral;

      if (!pos) return;

      const distance = this.ruler.distance([pos.lng, pos.lat], [lng, lat]);

      if (distance <= radius) {
        let idmember = element[0];
        if (idmember) {
          this.ids_of_members_inside_circle.push(idmember);
          marker.content = this.contentOfInsideMarker();
        }
      } else {
        marker.content = this.contentOfOutsideMarker();
      }
    });
  }

  /**
   * The content for a marker who's address falls inside the circle
   * @returns HTMLElement
   */
  contentOfInsideMarker() {
    const icon = document.createElement('div');
    icon.innerHTML = '<i class="fa-solid fa-check"></i>';
    return new google.maps.marker.PinElement({
      glyph: icon,
      glyphColor: 'black',
      background: 'lightgreen',
      borderColor: 'green',
      scale: 0.7,
    }).element;
  }

  /**
   * The content for a marker who's address falls outside the circle
   * @returns HTMLElement
   */
  contentOfOutsideMarker() {
    return new google.maps.marker.PinElement({
      glyph: 'X',
      glyphColor: 'grey',
      background: 'lightgrey',
      borderColor: 'grey',
      scale: 0.7,
    }).element;
  }

  /**
   * Called when th euser changes the radius of the circle
   * @param e
   */
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

  /** Called if the user selected a row in the address list */
  onIdSelected(idmember: number) {
    this.markers.forEach((element) => {
      let id = element[0];
      if (id && id == idmember) {
        google.maps.event.trigger(element[1], 'click');
      }
    });
  }

  /**
   * Required so that the template can access the Enum ListType
   * From {@link https://stackoverflow.com/a/59289208}
   */
  public get ListType() {
    return ListType;
  }
  /** Convenience getter for easy access to form fields */
  get f() {
    return this.form.controls;
  }
}
