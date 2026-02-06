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

  circle_center2: { lat: number; lng: number } = {
    lat: 51.499637,
    lng: -0.167121,
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

  // called after the view is initially rendered, to allow map to appear
  ngAfterViewInit() {
    this.center_of_circle_marker_options = {
      gmpDraggable: true,
    };
  }

  onDragEnd(event: google.maps.MapMouseEvent) {
    this.circle_center = event.latLng!.toJSON();
    this.circle_center_point.emit(this.circle_center);
  }

  /**
   * This is the event handler for when a marker is finished being initialized. I'm using
   * this event to make a configuratiuon change to the scale of the marker,
   * scaling it up by 1.5x.
   * @param marker The marker that was initialized
   */
  onCircleCenterMarkerInitialized(
    marker: google.maps.marker.AdvancedMarkerElement,
  ) {
    marker.content = new google.maps.marker.PinElement({
      scale: 1.5,
    });
  }

  /**
   * The content for a marker with address/location falling inside the circle
   * @returns HTMLElement
   */
  onInsideMarkerInitialized(marker: google.maps.marker.AdvancedMarkerElement) {
    // Use of 'as any' to avoid TypeScript error about invalid property in PinElementOptions
    marker.content = new google.maps.marker.PinElement({
      glyphText: '✓',
      glyphColor: 'black',
      background: 'lightgreen',
      borderColor: 'green',
      scale: 0.7,
    } as any);
  }

  /**
   * The content for a marker with address/location falling outside the circle
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
