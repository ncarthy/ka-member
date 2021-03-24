import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { FormBuilder, FormGroup, FormArray, Validators } from '@angular/forms';

import { Observable, Subject, BehaviorSubject } from 'rxjs';
import {
  debounceTime,
  distinctUntilChanged,
  switchMap,
  tap,
  map,
} from 'rxjs/operators';

import {
  CountryService,
  MemberFilterService,
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
export class MemberFilterComponent implements OnInit {
  @Output() filter: EventEmitter<MemberFilter> = new EventEmitter<MemberFilter>();
  @Output() loading: EventEmitter<boolean> = new EventEmitter<boolean>();
  @Output() filteredMembers: EventEmitter<
    MemberSearchResult[]
  > = new EventEmitter<MemberSearchResult[]>();

  form!: FormGroup;
  countries$!: Observable<Country[]>;
  membershipStatuses$!: Observable<MembershipStatus[]>;
  filterSubject: BehaviorSubject<MemberFilter> = new BehaviorSubject<MemberFilter>(
    new MemberFilter({removed: YesNoAny.NO}) 
  );
  filter$: Observable<MemberFilter> = this.filterSubject.asObservable();

  constructor(
    private formBuilder: FormBuilder,
    private countryService: CountryService,
    private MemberFilterService: MemberFilterService,
    private membershipStatusService: MembershipStatusService
  ) {
    this.membershipStatuses$ = this.membershipStatusService.getAll();
    this.countries$ = this.countryService.getAll();
  }

  // convenience getters for easy access to form fields
  get f() {
    return this.form.controls;
  }
  get dr() {
    return this.f.dateranges as FormArray;
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
      removed: ['no'],
      postonhold: ['any'],
      email1: ['any'],

      // selects (drop downs)
      membertypeid: [null],
      countryid: [null],
      paymenttypeid: [null],
      bankaccountid: [null],

      // date pickers
      dateranges: new FormArray([]),

      maxresults: [null],

      ignore: [null, Validators.required],
    });

    // Add one date range
    this.onAddDateRange();

    this.form.valueChanges
      .pipe(
        debounceTime(500),
        map(() => new MemberFilter(this.form.value))
      )
      .subscribe((filter: MemberFilter) => this.filterSubject.next(filter));

    this.filter$
      .pipe(
        map((filter: MemberFilter) => filter.toString()),
        distinctUntilChanged(),
        tap(() => this.loading.emit(true)),
        switchMap((urlParameters: string) =>
          this.MemberFilterService.filter(urlParameters)
        )
      )
      .subscribe((results: MemberSearchResult[]) => {
        this.filteredMembers.emit(results);
        this.filter.emit(this.filterSubject.value);
      })
      .add(this.loading.emit(false));
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
      dateRanges: {},
    });
  }
}
