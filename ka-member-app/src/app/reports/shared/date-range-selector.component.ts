import { CommonModule, KeyValue } from '@angular/common';
import { Component, EventEmitter, Input, Output } from '@angular/core';
import { FormGroup, ReactiveFormsModule } from '@angular/forms';
import { NgbDatepickerModule } from '@ng-bootstrap/ng-bootstrap';

import { DateRangeEnum } from '@app/_models';

@Component({
  selector: 'app-date-range-selector',
  templateUrl: './date-range-selector.component.html',
  imports: [CommonModule, NgbDatepickerModule, ReactiveFormsModule],
})
export class DateRangeSelectorComponent {
  @Input({ required: true }) form!: FormGroup;
  @Output() dateRangeChanged = new EventEmitter<string | null>();
  @Output() refreshClicked = new EventEmitter<void>();

  public get DateRange() {
    return DateRangeEnum;
  }

  originalOrder = (
    a: KeyValue<string, DateRangeEnum>,
    b: KeyValue<string, DateRangeEnum>,
  ): number => {
    return 0;
  };

  onDateRangeChanged(value: string) {
    this.dateRangeChanged.emit(value);
  }

  onRefreshClicked() {
    this.refreshClicked.emit();
  }
}
