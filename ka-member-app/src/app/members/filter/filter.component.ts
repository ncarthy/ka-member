import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { FormBuilder, FormGroup, FormArray } from '@angular/forms';

import { Observable, Subject, BehaviorSubject } from 'rxjs';
import { debounceTime, distinctUntilChanged, switchMap, tap } from 'rxjs/operators';

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
import { BinaryOperatorToken } from 'typescript';

@Component({
  selector: 'member-filter',
  templateUrl: './filter.component.html',
})
export class FilterComponent implements OnInit {
  @Output() loading: EventEmitter<boolean> = new EventEmitter<boolean>();
  @Output() filteredMembers: EventEmitter<
    MemberSearchResult[]
  > = new EventEmitter<MemberSearchResult[]>();

  loadingValue: boolean = false;
  form!: FormGroup;
  countries$!: Observable<Country[]>;
  membershipStatuses$!: Observable<MembershipStatus[]>;
  
  filter!: MemberFilter;
  filterSubject: Subject<MemberFilter> = new BehaviorSubject<MemberFilter>(new MemberFilter());
  filter$: Observable<MemberFilter> = this.filterSubject.asObservable();

  constructor(
    private formBuilder: FormBuilder,
    private countryService: CountryService,
    private memberSearchService: MemberSearchService,
    private membershipStatusService: MembershipStatusService
  ) {
    this.membershipStatuses$ = this.membershipStatusService.getAll();
    this.countries$ = this.countryService.getAll();

    this.loading.subscribe((value:boolean) => this.loadingValue = value);
  }

  // convenience getters for easy access to form fields
  get f() {
    return this.form.controls;
  }
  get dr() {
    return this.f.dateRanges as FormArray;
  }
  get dateRangesFormGroups() {
    return this.dr.controls as FormGroup[];
  }

  ngOnInit(): void {
    this.form = this.formBuilder.group({
      // Text
      businessorsurname: [null],
      address: [null],

      // checkboxes
      removed: ['any'],
      postonhold: ['any'],
      email1: ['any'],

      // selects (drop downs)
      statusID: [null],
      countryID: [null],
      paymentmethodID: [null],
      bankaccountID: [null],

      // date pickers
      dateRanges: new FormArray([]),

      maxresults:[null]
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
          this.filter.businessorsurname = this.f['businessorsurname'].value;
          this.filter.address = this.f['address'].value;
          this.filter.removed = this.f['removed'].value;
          this.filter.postonhold = this.f['postonhold'].value;
          this.filter.email1 = this.f['email1'].value;          
          this.filter.membertypeid = this.f['statusID'].value;
          this.filter.countryid = this.f['countryID'].value;
          this.filter.paymentmethodID = this.f['paymentmethodID'].value;
          this.filter.bankaccountID = this.f['bankaccountID'].value;

          this.filter.maxresults = this.f['maxresults'].value;
          this.filterSubject.next(this.filter);
          return this.filter$;
        }),
        distinctUntilChanged(),
        switchMap((filter: MemberFilter) => {
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
    this.dr.push(
      this.formBuilder.group({
        startDate: [startDate],
        endDate: [endDate],
        dateType: [dateType],
      })
    );

    return false; // Must return false from click event to stop it reloading the page
  }

  /* remove the selected date range object */
  onRemoveDateRange(index: number) {
    if (this.dr.length > 1 && index) {
      this.dr.removeAt(index);
    }

    return false; // Must return false from click event to stop it reloading the page
  }

  // Required so that the template can access the EnumS
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
