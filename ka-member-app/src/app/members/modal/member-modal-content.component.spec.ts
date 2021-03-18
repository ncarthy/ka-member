import { ComponentFixture, TestBed } from '@angular/core/testing';

import { MemberModalContentComponent } from './member-modal-content.component';

describe('MemberModalContentComponent', () => {
  let component: MemberModalContentComponent;
  let fixture: ComponentFixture<MemberModalContentComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ MemberModalContentComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(MemberModalContentComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
