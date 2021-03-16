import {
    Component,
    OnInit,
    Output,
    EventEmitter,
    ElementRef
  } from '@angular/core';
  
  // By importing just the rxjs operators we need, We're theoretically able
  // to reduce our build size vs. importing all of them.
  import { fromEvent } from 'rxjs';
  import { map, filter, debounceTime, tap, switchMap } from 'rxjs/operators';
  
  import { AddressSearchService } from '@app/_services';
  import { Address } from '@app/_models';
import { regExpEscape } from '@ng-bootstrap/ng-bootstrap/util/util';
  
  @Component({
    selector: 'address-search-box',
    template: `
      <input type="text" class="form-control" placeholder="Enter full postcode" autofocus>
    `
  })
  export class SearchBoxComponent implements OnInit {
    @Output() loading: EventEmitter<boolean> = new EventEmitter<boolean>();
    @Output() results: EventEmitter<Address[]> = new EventEmitter<Address[]>();
  
    constructor(private addressSearchService: AddressSearchService,
                private el: ElementRef) {
    }


    // Create an observalbe from the stream of characters being entered in the search box
    // and convert those values into an array of MemberSearchredult objects

    // See https://rxjs-dev.firebaseapp.com/guide/v6/migration for pipe format.
    ngOnInit(): void {

      // From https://stackoverflow.com/a/51885364/6941165
      /*const postcode = new RegExp("^[A-Z]{1,2}\d[A-Z\d]? ?\d[A-Z]{2}$", "i");      //'i' = case insensitive
      const postcode1 = new RegExp("^([A-Za-z][A-Ha-hJ-Yj-y]?[0-9][A-Za-z0-9]? ?[0-9][A-Za-z]{2}|[Gg][Ii][Rr] ?0[Aa]{2})$");*/
      const postcode2 = new RegExp("^([A-Z][A-HJ-Y]?[0-9][A-Z0-9]? ?[0-9][A-Z]{2}|GIR ?0A{2})$", "i");
      /*const postcode3 = new RegExp("^([A-Z][A-HJ-Y]?\d[A-Z\d]? ?\d[A-Z]{2}|GIR ?0A{2})$", "i"); // didn't work
      const postcode4 = new RegExp("^([A-Z][A-HJ-Y]?[0-9][A-Z0-9]? ?[0-9][A-Z]{2})$", "i");*/

      /*console.log("Try 1", postcode.test("sw7 1jy"));
      console.log("Try 2", postcode.test("SW7 1JY"));
      console.log("Try 3", postcode.test("SW71JY"));
      const test = new RegExp('SW7',"i");
      console.log("Try 4", test.test("SW71JY"));
      console.log("Try 5", test.test("sw71JY"));
      console.log("Try 6", postcode1.test("sw71JY"));
      console.log("Try 7", postcode1.test("sw7 1JY"));
      console.log("Try 8", postcode2.test("sw7 1JY"));
      console.log("Try 9", postcode3.test("sw7 1JY"));
      console.log("Try 10", postcode4.test("sw7 1JY"));*/

        // convert the `keyup` event into an observable stream
        fromEvent(this.el.nativeElement, 'keyup').pipe(
          map((e: any) => e.target.value),            // extract the value of the input
                    
          filter((text: string) => text.length > 3 && postcode2.test(text)),  // filter out if invalid postcode
          
          debounceTime(250),                          // only once every 250ms
          
          tap((query: string) => this.loading.emit(true)),         // enable loading

          // search, discarding old events if new input comes in
          switchMap((query: string) => this.addressSearchService.search(query))
        )
        // act on the return of the search
        .subscribe(
            (results: Address[]) => { // on sucesss
                this.loading.emit(false);                
                this.results.emit(results);
            },
            (err: any) => { // on error
                console.log(err);
                this.loading.emit(false);
            },
            () => { // on completion
                this.loading.emit(false);
            }
        );
    }
}