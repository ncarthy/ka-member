import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';

import {
  NgbModule,
  NgbDateAdapter  
} from '@ng-bootstrap/ng-bootstrap';
import { MembersRoutingModule } from './members-routing.module';
import { SharedModule } from '@app/shared/shared.module';
import { NgbUTCStringAdapter } from '@app/_helpers';

import { LayoutComponent } from './layout.component';
import { ListComponent } from './list.component';
import { RowComponent } from './row.component';
import { AddEditComponent } from './add-edit/add-edit.component';
import { ManageComponent } from './manage/manage.component';

@NgModule({
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MembersRoutingModule,
    NgbModule,
    SharedModule,
  ],
  declarations: [
    LayoutComponent,
    ListComponent,
    AddEditComponent,
    RowComponent,
    ManageComponent
  ],
  providers: [{ provide: NgbDateAdapter, useClass: NgbUTCStringAdapter }], // From https://stackoverflow.com/a/47945155/6941165
})
export class MembersModule {}
