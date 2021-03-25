import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';

import { NgbModule, NgbDateAdapter } from '@ng-bootstrap/ng-bootstrap';
import { MembersRoutingModule } from './members-routing.module';
import { SharedModule } from '@app/shared/shared.module';
import { NgbUTCStringAdapter } from '@app/_helpers';
import { MemberFilterService } from '@app/_services';

import { MemberLayoutComponent } from './layout.component';
import { MemberListComponent, MemberRowComponent } from './list';
import { MemberAddEditComponent } from './add-edit/add-edit.component';
import { MemberManageComponent } from './manage/manage.component';
import { MemberFilterComponent } from './filter/filter.component';
import {
  TransactionAddEditComponent,
  TransactionListComponent,
  TransactionRowComponent,
} from './transactions';
import {
  MemberAnonymizeConfirmModalComponent,
  MemberDeleteConfirmModalComponent,
  TransactionDeleteConfirmModalComponent,
} from './modals';

@NgModule({
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MembersRoutingModule,
    NgbModule,
    SharedModule,
  ],
  declarations: [
    MemberLayoutComponent,
    MemberListComponent,
    MemberRowComponent,
    MemberFilterComponent,
    MemberAddEditComponent,
    MemberManageComponent,
    TransactionListComponent,
    TransactionRowComponent,
    MemberAnonymizeConfirmModalComponent,
    MemberDeleteConfirmModalComponent,
    TransactionDeleteConfirmModalComponent,
    TransactionAddEditComponent,
  ],
  providers: [
    { provide: NgbDateAdapter, useClass: NgbUTCStringAdapter },
    MemberFilterService,
  ],
  // NgbDateAdapter to handle MySQL date format (From https://stackoverflow.com/a/47945155/6941165 )
})
export class MembersModule {}
