import {
  Component,
  Input,
  OnInit,
  Output,
  EventEmitter,
  ElementRef,
} from '@angular/core';

import { AddressSearchService } from '@app/_services';
import { GetAddressIOAddress } from '@app/_models';

@Component({
  selector: 'address-search-box2',
  template: `
    <div class="input-group">

        <span class="input-group-text col-4" id="basic-addon1"
          ><i class="fas fa-search"></i
        ></span>
   
      <input
        type="text"
        class="form-control"
        placeholder="Start typing..."
        id="autocompleteSearchBox"
      />
    </div>
  `,
})
export class SearchBox2Component implements OnInit {
  @Output() loading: EventEmitter<boolean> = new EventEmitter<boolean>();
  @Output() results: EventEmitter<GetAddressIOAddress[]> = new EventEmitter<
    GetAddressIOAddress[]
  >();
  @Input() disable: boolean = false;

  private autocomplete!: google.maps.places.Autocomplete;

  constructor(
    private addressSearchService: AddressSearchService,
    private el: ElementRef,
  ) {}

  /**
   * Create an observable from the stream of characters being entered in the search box
   * and convert those values into an array of GetAddressIOAddress objects
   */
  ngOnInit(): void {
    if (this.disable) {
      return;
    }

    let address1Field: HTMLInputElement;
    address1Field = document.querySelector(
      '#autocompleteSearchBox',
    ) as HTMLInputElement;
    this.autocomplete = new google.maps.places.Autocomplete(address1Field, {
      componentRestrictions: { country: ['uk'] },
      fields: ['address_components', 'geometry'],
      types: ['address'],
    });

    this.autocomplete.addListener('place_changed', this.fillInAddress);

    // convert the `keyup` event into an observable stream
    /*fromEvent(this.el.nativeElement, 'keyup')
        .pipe(
          map((e: any) => e.target.value), // extract the value of the input
  
          filter((text: string) => text.length > 3 ), // filter out if too short
  
          debounceTime(250), // only once every 250ms
  
          tap((query: string) => this.loading.emit(true)), // enable loading
  
          // search, discarding old events if new input comes in
          switchMap((query: string) => this.addressSearchService.search(query))
        )
        // act on the return of the search
        .subscribe({
          next: (results: GetAddressIOAddress[]) => {
            // on sucesss
            this.loading.emit(false);
            this.results.emit(results);
          },
          error: (err: any) => {
            // on error
            console.log(err);
            this.loading.emit(false);
          },
          complete: () => {
            // on completion
            this.loading.emit(false);
          },
        });*/
  }

  fillInAddress() {
    // Get the place details from the autocomplete object.
    const place = this.autocomplete.getPlace();

    // Get each component of the address from the place details,
    // and then fill-in the corresponding field on the form.
    // place.address_components are google.maps.GeocoderAddressComponent objects
    // which are documented at http://goo.gle/3l5i5Mr
    for (const component of place.address_components as google.maps.GeocoderAddressComponent[]) {
      console.log(
        component.types[0] +
          ' . ' +
          component.short_name +
          ' . ' +
          component.long_name,
      );
    }
  }
}
