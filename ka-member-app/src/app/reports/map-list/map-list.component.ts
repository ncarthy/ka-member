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
import { MapMarker } from '@app/_models';
import { Observable, merge, of } from 'rxjs';
import CheapRuler from 'cheap-ruler';

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
  radius: number = 200;
  ruler: CheapRuler = new CheapRuler(this.lat, 'meters');
  postcodesInCircle: string[] = new Array();

  mapOptions: google.maps.MapOptions = {
    center: new google.maps.LatLng(this.lat, this.lng),
    zoom: 16,
  };

  constructor(private membersService: MembersService) {
    // Create an Observable of Google Maps markers
    this.markers$ = this.membersService.getMapList().pipe(
      switchMap((members: MapMarker[]) => {
        const obs = members.map((x) => {

          let m: google.maps.Marker = this.createMarker(x);
          return of(m);
        });
        return merge(...obs);
      })
    );
  }

  private createMarker(x: MapMarker) {
    const infoWindow = new google.maps.InfoWindow({
      content: `<p>${x.postcode}</p><p>No. of Members: ${x.count}</p>`,
    });
    let m: google.maps.Marker = new google.maps.Marker({
      position: new google.maps.LatLng(x.gpslat, x.gpslng),
      map: this.map,
      icon: {
        path: google.maps.SymbolPath.BACKWARD_CLOSED_ARROW,
        scale: 3,
      },
      label: x.postcode
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

    this.addCircleToMap(this.lat, this.lng);

    this.markers$.subscribe((m: google.maps.Marker) => {
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
    });
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

    this.markers.forEach((element) => {
      const pos = element.getPosition();

      if (!pos) return;

      const distance = this.ruler.distance(
        [pos.lat(), pos.lng()],
        [lat, lng]
      );

      if (distance <= this.radius) {
        element.setOpacity(1);
        //this.postcodesInCircle.push(element.getLabel()?.text);        
      } else {
        element.setOpacity(0.5);
      }
    });

    this.map.setCenter(event.latLng);
  }

  
}
