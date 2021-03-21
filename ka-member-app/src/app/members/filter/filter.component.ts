import { Component, EventEmitter, OnInit, Output } from '@angular/core';

import { BehaviorSubject, Observable } from 'rxjs';
import { map } from 'rxjs/operators';

import { MemberSearchService } from '@app/_services';
import { MemberSearchResult, YesNoAny } from '@app/_models';

@Component({
  selector: 'member-filter',
  templateUrl: './filter.component.html',
})
export class FilterComponent implements OnInit {
  @Output() loading: EventEmitter<boolean> = new EventEmitter<boolean>();
  @Output() results: EventEmitter<MemberSearchResult[]> = new EventEmitter<
    MemberSearchResult[]
  >();

  private membersSubject: BehaviorSubject<MemberSearchResult[]>;
  public members: Observable<MemberSearchResult[]>;

  public get membersValue(): MemberSearchResult[] {
    return this.membersSubject.value;
  }

  constructor(private memberSearchService: MemberSearchService) {
    this.membersSubject = new BehaviorSubject<MemberSearchResult[]>([]);
    this.members = this.membersSubject.asObservable();
  }

  ngOnInit(): void {
    this.memberSearchService.getAll().pipe().subscribe((results: MemberSearchResult[]) => { // on sucesss
      this.loading.emit(false);
      this.membersSubject.next(results);
      this.results.emit(results);
  });
  }
}
