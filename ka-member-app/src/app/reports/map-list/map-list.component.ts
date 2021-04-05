import {
  Component,
  AfterViewInit,
  ViewChild,
  ElementRef,
  OnInit,
} from '@angular/core';
import {} from 'googlemaps';
import { MembersService } from '@app/_services';
import { switchMap } from 'rxjs/operators';
import { MemberSearchResult } from '@app/_models';
import { Observable, merge, of } from 'rxjs';

@Component({
  templateUrl: './map-list.component.html',
})
export class MapListComponent implements OnInit {
  @ViewChild('mapContainer', { static: false }) gmap!: ElementRef;
  map!: google.maps.Map;
  lat = 51.499063;
  lng = -0.165382;
  marker!: google.maps.Marker;
  loading: boolean = false;
  markers$!: Observable<google.maps.Marker>;
  markers: google.maps.Marker[] = new Array();
  circle!: google.maps.Circle;

  mapOptions: google.maps.MapOptions = {
    center: new google.maps.LatLng(this.lat, this.lng),
    zoom: 15,
  };

  constructor(private membersService: MembersService) {
    // Create an Observable of Google Maps markers
    this.markers$ = this.membersService.getMapList().pipe(
      switchMap((members: MemberSearchResult[]) => {
        const obs = members.map((x) => {
          return of(
            new google.maps.Marker({
              position: new google.maps.LatLng(x.gpslat, x.gpslong),
              map: this.map,
              icon: {
                path: google.maps.SymbolPath.BACKWARD_CLOSED_ARROW,
                scale: 3,
              },
            })
          );
        });
        return merge(...obs);
      })
    );
  }

  ngOnInit(): void {}

  ngAfterViewInit() {
    this.mapInitializer();
  }

  mapInitializer() {
    this.map = new google.maps.Map(this.gmap.nativeElement, this.mapOptions);
    this.marker = new google.maps.Marker({
      position: new google.maps.LatLng(this.lat, this.lng),
      map: this.map,
      draggable: true,
    });
    google.maps.event.addListener(
      this.marker,
      'dragend',
      (event: google.maps.MapMouseEvent) => this.drawCircleOnDragend(event)
    );
    this.marker.setMap(this.map);

    this.markers$.subscribe((m: google.maps.Marker) => {
      m.setMap(this.map);
      this.markers.push(m);
    });
  }

  
  drawCircleOnDragend(event: google.maps.MapMouseEvent) {
    
    if (this.circle) {
      this.circle.setMap(null); // remove from map
    }

    // add new circle
    this.circle = new google.maps.Circle({
      strokeColor: '#FF0000',
      strokeOpacity: 0.8,
      strokeWeight: 2,
      fillColor: '#FF0000',
      fillOpacity: 0.35,
      map: this.map,
      center: {
        lat: event.latLng.lat(),
        lng: event.latLng.lng(),
      },
      radius: 200,
    });
    //this.map.fitBounds(antennasCircle.getBounds());

    this.findMarkersInArea();
  }

  findMarkersInArea() {
    if (!this.circle) return;

    const center: google.maps.LatLng = this.circle.getCenter();
    const radius: number = this.circle.getRadius();

    this.markers.forEach(element => {
      const pos = element.getPosition();

      if (pos && google.maps.geometry.spherical.computeDistanceBetween(
        pos, center) <= radius) {
          // inside circle
          element.setOpacity(1);
        } else {
          element.setOpacity(0.5);
        }
    });


    
  }

}
