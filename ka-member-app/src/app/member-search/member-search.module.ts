import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MemberSearchService } from '@app/_services';
import { MemberSearchComponent } from './member-search.component';
import { SearchBoxComponent } from './search-box.component';
import { SearchResultComponent } from './search-result.component'

@NgModule({
  declarations: [
    MemberSearchComponent, 
    SearchBoxComponent,
    SearchResultComponent
  ],
  exports: [
    MemberSearchComponent, 
    SearchBoxComponent,
    SearchResultComponent
  ],
  imports: [
    CommonModule
  ],
  providers: [MemberSearchService]
})
export class MemberSearchModule { }
