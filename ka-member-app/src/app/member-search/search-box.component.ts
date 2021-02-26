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
  
  import { MemberSearchService } from '@app/_services';
  import { MemberSearchResult } from '@app/_models';
  
  @Component({
    selector: 'member-search-box',
    template: `
      <input type="text" class="form-control" placeholder="Surname or Business Name" autofocus>
    `
  })
  export class SearchBoxComponent implements OnInit {
    @Output() loading: EventEmitter<boolean> = new EventEmitter<boolean>();
    @Output() results: EventEmitter<MemberSearchResult[]> = new EventEmitter<MemberSearchResult[]>();
  
    constructor(private memberSearchService: MemberSearchService,
                private el: ElementRef) {
    }


    // Create an observalbe from the stream of characters being entered in the search box
    // and convert those values into an array of MemberSearchredult objects

    // See https://rxjs-dev.firebaseapp.com/guide/v6/migration for pipe format.
    ngOnInit(): void {
        // convert the `keyup` event into an observable stream
        fromEvent(this.el.nativeElement, 'keyup').pipe(
          map((e: any) => e.target.value),            // extract the value of the input
          filter((text: string) => text.length > 1),  // filter out if empty
          debounceTime(250),                          // only once every 250ms
          tap(() => this.loading.emit(true)),         // enable loading
          // search, discarding old events if new input comes in
          switchMap((query: string) => this.memberSearchService.search(query))
        )
        // act on the return of the search
        .subscribe(
            (results: MemberSearchResult[]) => { // on sucesss
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