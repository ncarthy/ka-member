import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';

import {
  NgbModule,
  NgbDateAdapter,
  NgbDateNativeAdapter,
} from '@ng-bootstrap/ng-bootstrap';
import { MembersRoutingModule } from './members-routing.module';
import { SharedModule } from '@app/shared/shared.module';
import { NgbUTCStringAdapter } from '@app/_helpers';

import { LayoutComponent } from './layout.component';
import { ListComponent } from './list.component';
import { AddEditComponent } from './add-edit.component';
import { RowComponent } from './row.component';

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
  ],
  providers: [{ provide: NgbDateAdapter, useClass: NgbUTCStringAdapter }],
})
export class MembersModule {}
