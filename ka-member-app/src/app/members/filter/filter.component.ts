import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { FormBuilder, FormGroup, FormArray } from '@angular/forms';

import { Observable } from 'rxjs';
import { debounceTime, switchMap, tap } from 'rxjs/operators';

import {
  CountryService,
  MemberSearchService,
  MembershipStatusService,
} from '@app/_services';
import {
  Country,
  MemberFilter,
  MemberSearchResult,
  MembershipStatus
} from '@app/_models';

@Component({
  selector: 'member-filter',
  templateUrl: './filter.component.html',
})
export class FilterComponent implements OnInit {
  @Output() loading: EventEmitter<boolean> = new EventEmitter<boolean>();
  @Output() filteredMembers: EventEmitter<
    MemberSearchResult[]
  > = new EventEmitter<MemberSearchResult[]>();

  form!: FormGroup;
  countries$!: Observable<Country[]>;
  membershipStatuses$!: Observable<MembershipStatus[]>;

  constructor(
    private formBuilder: FormBuilder,
    private countryService: CountryService,
    private memberSearchService: MemberSearchService,
    private membershipStatusService: MembershipStatusService
  ) {
    this.membershipStatuses$ = this.membershipStatusService.getAll();
    this.countries$ = this.countryService.getAll();
  }

  // convenience getters for easy access to form fields
  get f() {
    return this.form.controls;
  }
  get d() {
    return this.f.dateRanges as FormArray;
  }
  get dateRangesFormGroups() {
    return this.d.controls as FormGroup[];
  }

  ngOnInit(): void {
    this.form = this.formBuilder.group({
      // Text
      bizOrSurname: [null],
      address: [null],

      // checkboxes
      includeInactive: [false],
      postOnHold: [false],
      hasEmail: [false],

      // selects (drop downs)
      statusID: [null],
      countryID: [null],
      paymentMethod: [null],

      // date pickers
      dateRanges: new FormArray([]),
    });

    // Add one date range
    this.onAddDateRange();

    this.form.valueChanges
      .pipe(
        debounceTime(500),
        tap(() => this.loading.emit(true)),
        // search, discarding old events if new input comes in
        switchMap(() => {
          const filter = new MemberFilter();
          filter.businessorsurname = this.f['bizOrSurname'].value;
          filter.membertypeid = this.f['statusID'].value;
          console.log(filter.toString());
          return this.memberSearchService.filter(filter);})
      )
      .subscribe((results: MemberSearchResult[]) => {
        // on sucesss
        this.loading.emit(false);
        this.filteredMembers.emit(results);
      });
  }

  /* Add a new date range to the template */
  onAddDateRange(startDate = '', endDate = '', dateType = '') {
    this.d.push(
      this.formBuilder.group({
        startDate: [startDate],
        endDate: [endDate],
        dateType: [dateType],
      })
    );
  }

  /* remove the selected date range object */
  onRemoveDateRange(index: number) {
    if (this.d.length > 1 && index) {
      this.d.removeAt(index);
    }
  }
}
