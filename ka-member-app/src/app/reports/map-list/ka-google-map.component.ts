import {
  Component,
  EventEmitter,
  Input,
  AfterViewInit,
  Output,
} from '@angular/core';

// Docs at https://github.com/angular/components/blob/main/src/google-maps
import { GoogleMap, MapAdvancedMarker, MapCircle } from '@angular/google-maps';

@Component({
  selector: 'ka-google-map',
  imports: [GoogleMap, MapAdvancedMarker, MapCircle],
  templateUrl: './ka-google-map.component.html',
  styleUrl: './ka-google-map.component.css',
})
export class KAGoogleMapComponent implements AfterViewInit {
  // Default Center of map
  static readonly LAT = 51.499063;
  static readonly LNG = -0.165382;
  readonly CENTER: google.maps.LatLngLiteral = {
    lat: KAGoogleMapComponent.LAT,
    lng: KAGoogleMapComponent.LNG,
  };

  // Default radius of Map
  static readonly RADIUS = 200;

  // Default parameters of Circle
  readonly CIRCLE_OPTIONS = {
    strokeColor: 'red',
    strokeOpacity: 0.8,
    strokeWeight: 2,
    fillColor: 'red',
    fillOpacity: 0.35,
  };

  // Required for Advanced Markers
  readonly MAPID = '879f7bdf49a5142f6e525637'; // from Google Cloud Console

  @Input() circle_radius: number = KAGoogleMapComponent.RADIUS;
  @Input() inside_marker_positions: google.maps.LatLngLiteral[] = [];
  @Input() outside_marker_positions: google.maps.LatLngLiteral[] = [];

  @Output() circle_center_point: EventEmitter<google.maps.LatLngLiteral> =
    new EventEmitter<google.maps.LatLngLiteral>();

  circle_center: { lat: number; lng: number } = {
    lat: KAGoogleMapComponent.LAT,
    lng: KAGoogleMapComponent.LNG,
  };

  markers_inside_circle: number[] = [];

  mapOptions = {
    center: this.CENTER,
    zoom: 16,
    mapId: this.MAPID,
  };

  center_of_circle_marker_options: google.maps.marker.AdvancedMarkerElementOptions =
    {
      gmpDraggable: true,
    };

  ngAfterViewInit() {

    this.center_of_circle_marker_options = {
      gmpDraggable: true,
    };
  }

  onDragEnd(event: google.maps.MapMouseEvent) {
    this.circle_center = event.latLng!.toJSON();
    this.circle_center_point.emit(this.circle_center);
  }

  // this is called when the marker is initialized
  onMarkerInitialized(marker: google.maps.marker.AdvancedMarkerElement) {
    marker.content =  new google.maps.marker.PinElement({
      scale: 1.5,
    });
  }

  /**
   * The content for a marker who's address falls inside the circle
   * @returns HTMLElement
   */
  contentOfInsideMarker() {
    // Use of 'as any' to avoid TypeScript error about invalid property in PinElementOptions
    return new google.maps.marker.PinElement({
      glyphText: '✓',
      glyphColor: 'black',
      background: 'lightgreen',
      borderColor: 'green',
      scale: 0.7,
    } as any).element;
  }

  /**
   * The content for a marker who's address falls outside the circle
   * @returns HTMLElement
   */
  contentOfOutsideMarker() {
    // Use of 'as any' to avoid TypeScript error about invalid property in PinElementOptions
    return new google.maps.marker.PinElement({
      glyphText: 'X',
      glyphColor: 'grey',
      background: 'lightgrey',
      borderColor: 'grey',
      scale: 0.7,
    } as any).element;
  }
}
