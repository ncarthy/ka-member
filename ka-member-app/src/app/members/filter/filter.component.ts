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
  MembershipStatus,
  YesNoAny,
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
  filter!: MemberFilter;

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
      includeInactive: ['any'],
      postOnHold: ['any'],
      hasEmail: ['any'],

      // selects (drop downs)
      statusID: [null],
      countryID: [null],
      paymentMethodID: [null],
      bankAccountID: [null],

      // date pickers
      dateRanges: new FormArray([]),

      maxResults:[null]
    });

    // Add one date range
    this.onAddDateRange();

    this.form.valueChanges
      .pipe(
        debounceTime(500),
        tap(() => this.loading.emit(true)),
        // search, discarding old events if new input comes in
        switchMap(() => {
          this.filter = new MemberFilter();
          this.filter.businessorsurname = this.f['bizOrSurname'].value;
          this.filter.address = this.f['address'].value;
          this.filter.removed = this.f['includeInactive'].value;
          this.filter.postonhold = this.f['postOnHold'].value;
          this.filter.email1 = this.f['hasEmail'].value;          
          this.filter.membertypeid = this.f['statusID'].value;
          this.filter.countryid = this.f['countryID'].value;
          this.filter.paymentmethodID = this.f['paymentMethodID'].value;
          this.filter.bankaccountID = this.f['bankAccountID'].value;

          this.filter.maxresults = this.f['maxResults'].value;
          return this.memberSearchService.filter(this.filter);
        })
      )
      .subscribe((results: MemberSearchResult[]) => {
        // on sucess        
        this.filteredMembers.emit(results);
      }).add(this.loading.emit(false));
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

  // Required so that the template can access the Enum
  // From https://stackoverflow.com/a/59289208
  public get YesNoAny() {
    return YesNoAny;
  }

  onReset() {
  
    this.form.reset({
      dateRanges: {}
    });
  }
}
