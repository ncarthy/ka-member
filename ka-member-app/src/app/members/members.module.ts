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
import { ListComponent } from './list/list.component';
import { RowComponent } from './list/row.component';
import { AddEditComponent } from './add-edit/add-edit.component';
import { ManageComponent } from './manage/manage.component';
import { MemberSearchBoxComponent } from './filter/search-box.component';
import { MemberSearchService } from '@app/_services';
import { FilterComponent } from './filter/filter.component';

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
    ManageComponent,
    MemberSearchBoxComponent,
    FilterComponent
  ],
  providers: [{ provide: NgbDateAdapter, useClass: NgbUTCStringAdapter }, MemberSearchService], // From https://stackoverflow.com/a/47945155/6941165
})
export class MembersModule {}
