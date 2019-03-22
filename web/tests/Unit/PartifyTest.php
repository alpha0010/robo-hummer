<?php

namespace Tests\Unit;

use App\Media;
use Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ClearMedia;
use Tests\TestCase;

class PartifyTest extends TestCase
{
    use RefreshDatabase;
    use ClearMedia;

    public function test1()
    {
        $this->fileTest("01a-Pitches-Pitches.xml");
        $this->fileTest("01b-Pitches-Intervals.xml");
        $this->fileTest("01c-Pitches-NoVoiceElement.xml", 89);
        $this->fileTest("01d-Pitches-Microtones.xml");
        $this->fileTest("01ea-Pitches-Parenthesis-Changed-Accidentals.xml");
        $this->fileTest("01e-Pitches-EditorialCautionaryAccidentals.xml");
        $this->fileTest("01f-Pitches-ParenthesizedMicrotoneAccidentals.xml");
    }

    public function test2()
    {
        $this->fileTest("02a-Rests-Durations.xml", 79);
        $this->fileTest("02b-Rests-PitchedRests.xml", 77);
        $this->fileTest("02c-Rests-MultiMeasureRests.xml", 79);
        $this->fileTest("02d-Rests-Multimeasure-TimeSignatures.xml", 75);
        $this->fileTest("02e-Rests-NoType.xml", 72);
    }

    public function test3()
    {
        $this->fileTest("03aa-Rhythm-Durations.xml");
        $this->fileTest("03ab-Rhythm-Durations.xml", 86);
        $this->fileTest("03b-Rhythm-Backup.xml", 67);
        $this->fileTest("03c-Rhythm-DivisionChange.xml");
        $this->fileTest("03d-Rhythm-DottedDurations-Factors.xml", 81);
    }

    /**
     * @brief "Time signatures" should be preserved when it is partified.
     */
    public function test11()
    {
        $this->fileTest("11a-TimeSignatures.xml", 73);
        //$this->fileTest("11b-TimeSignatures-NoTime.xml");
        $this->fileTest("11c-TimeSignatures-CompoundSimple.xml");
        $this->fileTest("11d-TimeSignatures-CompoundMultiple.xml");
        $this->fileTest("11e-TimeSignatures-CompoundMixed.xml");
        $this->fileTest("11f-TimeSignatures-SymbolMeaning.xml", 86);
        $this->fileTest("11g-TimeSignatures-SingleNumber.xml");
        $this->fileTest("11h-TimeSignatures-SenzaMisura.xml");
    }

    public function test12()
    {
        $this->fileTest("12aa-Clefs_Pitch_Traditional.xml");
        $this->fileTest("12ab-Clefs-Percussion-NonTrad.xml");
        $this->fileTest("12ac-Clefs-TAB-Switch.xml");
        $this->fileTest("12ad-Clefs-Extreme-Octave.xml");
        $this->fileTest("12b-Clefs-NoKeyOrClef.xml");
    }

    public function test13()
    {
        $this->fileTest("13aa-KeySignatures-Extreme.xml", 65);
        $this->fileTest("13ab-KeySignatures-Cancel.xml", 78);
        $this->fileTest("13ac-KeySignatures-Octaves.xml", 86);
        $this->fileTest("13a-KeySignatures.xml", 77);
        $this->fileTest("13b-KeySignatures-ChurchModes.xml", 72);
        //$this->fileTest("13c-KeySignatures-NonTraditional.xml");
        //$this->fileTest("13d-KeySignatures-Microtones.xml");
        $this->fileTest("13e-KeySignatures-MidMeasure-Change.xml", 79);
    }

    public function test14()
    {
        $this->fileTest("14a-StaffDetails-LineChanges.xml");
    }

    public function test21()
    {
        $this->fileTest("21a-Chord-Basic.xml", 68);
        $this->fileTest("21b-Chords-TwoNotes.xml", 59);
        $this->fileTest("21c-Chords-ThreeNotesDuration.xml", 37);
        $this->fileTest("21d-Chords-SchubertStabatMater.xml", 57);
        $this->fileTest("21e-Chords-PickupMeasures.xml", 49);
        $this->fileTest("21f-Chord-ElementInBetween.xml");
    }

    public function test22()
    {
        $this->fileTest("22a-Noteheads.xml");
        $this->fileTest("22b-Staff-Notestyles.xml");
        $this->fileTest("22c-Noteheads-Chords.xml", 46);
        $this->fileTest("22d-Parenthesized-Noteheads.xml", 46);
    }

    public function test23()
    {
        $this->fileTest("23a-Tuplets.xml", 85);
        $this->fileTest("23b-Tuplets-Styles.xml", 65);
        $this->fileTest("23c-Tuplet-Display-NonStandard.xml", 65);
        $this->fileTest("23d-Tuplets-Nested.xml", 87);
        $this->fileTest("23e-Tuplets-Tremolo.xml", 88);
        $this->fileTest("23f-Tuplets-DurationButNoBracket.xml", 83);
    }

    public function test24()
    {
        $this->fileTest("24a-GraceNotes.xml", 76);
        $this->fileTest("24b-ChordAsGraceNote.xml", 61);
        //$this->fileTest("24c-GraceNote-MeasureEnd.xml");
        //$this->fileTest("24d-AfterGrace.xml");
        $this->fileTest("24e-GraceNote-StaffChange.xml", 87);
        $this->fileTest("24f-GraceNote-Slur.xml");
    }

    public function test31()
    {
        $this->fileTest("31a-Directions.xml", 48);
        $this->fileTest("31c-MetronomeMarks.xml", 71);
    }

    public function test32()
    {
        $this->fileTest("32aa-Notations2_Ornaments.xml", 71);
        //$this->fileTest("32ab-Notations3.xml");
        $this->fileTest("32ac-Notations4.xml");
        $this->fileTest("32a-Notations.xml", 71);
        $this->fileTest("32b-Articulations-Texts.xml");
        $this->fileTest("32c-MultipleNotationChildren.xml");
        $this->fileTest("32d-Arpeggio.xml", 39);
    }

    public function test33()
    {
        $this->fileTest("33a-Spanners.xml");
        $this->fileTest("33b-Spanners-Tie.xml");
        $this->fileTest("33c-Spanners-Slurs.xml");
        $this->fileTest("33d-Spanners-OctaveShifts.xml");
        $this->fileTest("33e-Spanners-OctaveShifts-InvalidSize.xml");
        $this->fileTest("33f-Trill-EndingOnGraceNote.xml", 72);
        $this->fileTest("33g-Slur-ChordedNotes.xml", 51);
        $this->fileTest("33h-Spanners-Glissando.xml");
        $this->fileTest("33i-Ties-NotEnded.xml");
    }

    /**
     * @brief "Multiple parts" tests are the most important tests for the partify script.
     */
    public function test41()
    {
        $this->fileTest("41a-MultiParts-Partorder.xml", 67);
        $this->fileTest("41b-MultiParts-MoreThan10.xml", 11);
        $this->fileTest("41c-StaffGroups.xml", 44);
        $this->fileTest("41d-StaffGroups-Nested.xml", 75);
        $this->fileTest("41e-StaffGroups-InstrumentNames-Linebroken.xml");
        $this->fileTest("41f-StaffGroups-Overlapping.xml", 27);
        $this->fileTest("41g-PartNoId.xml", 74);
        $this->fileTest("41h-TooManyParts.xml", 74);
        $this->fileTest("41i-PartNameDisplay-Override.xml", 88);
    }

    /**
     * @brief "Multiple voices per staff"
     */
    public function test42()
    {
        // TODO: Test that the lyrics remain with their specific notes.
        $this->fileTest("42a-MultiVoice-TwoVoicesOnStaff-Lyrics.xml", 56);
        // TODO: Test that the rhythm isn't messed up by the rests.
        $this->fileTest("42b-MultiVoice-MidMeasureClefChange.xml", 61);
    }

    /**
     * @brief "One part on multiple staves"
     */
    public function test43()
    {
        $this->fileTest("43a-PianoStaff.xml");
        $this->fileTest("43b-MultiStaff-DifferentKeys.xml");
        $this->fileTest("43c-MultiStaff-DifferentKeysAfterBackup.xml");
        $this->fileTest("43d-MultiStaff-StaffChange.xml", 33);
        $this->fileTest("43e-Multistaff-ClefDynamics.xml", 88);
    }

    public function test45()
    {
        $this->fileTest("45a-SimpleRepeat.xml", 79);
        $this->fileTest("45b-RepeatWithAlternatives.xml");
        $this->fileTest("45c-RepeatMultipleTimes.xml", 79);
        $this->fileTest("45d-Repeats-Nested-Alternatives.xml", 79);
        $this->fileTest("45e-Repeats-Nested-Alternatives.xml", 79);
        $this->fileTest("45f-Repeats-InvalidEndings.xml", 79);
        $this->fileTest("45g-Repeats-NotEnded.xml");
    }

    public function test46()
    {
        $this->fileTest("46a-Barlines.xml", 79);
        $this->fileTest("46b-MidmeasureBarline.xml");
        $this->fileTest("46c-Midmeasure-Clef.xml");
        $this->fileTest("46d-PickupMeasure-ImplicitMeasures.xml");
        $this->fileTest("46e-PickupMeasure-SecondVoiceStartsLater.xml", 68);
        $this->fileTest("46f-IncompleteMeasures.xml");
        $this->fileTest("46g-PickupMeasure-Chordnames-FiguredBass.xml", 55);
    }

    public function test51()
    {
        $this->fileTest("51b-Header-Quotes.xml", 74);
        $this->fileTest("51c-MultipleRights.xml", 74);
        $this->fileTest("51d-EmptyTitle.xml", 74);
    }

    public function test52()
    {
        $this->fileTest("52a-PageLayout.xml", 78);
        $this->fileTest("52b-Breaks.xml");
    }

    /**
     * @brief "Lyrics" should all be preserved.
     */
    public function test61()
    {
        $this->fileTest("61a-Lyrics.xml");
        $this->fileTest("61b-MultipleLyrics.xml");
        $this->fileTest("61c-Lyrics-Pianostaff.xml");
        $this->fileTest("61d-Lyrics-Melisma.xml", 67);
        $this->fileTest("61e-Lyrics-Chords.xml", 52);
        $this->fileTest("61f-Lyrics-GracedNotes.xml");
        $this->fileTest("61g-Lyrics-NameNumber.xml");
        $this->fileTest("61h-Lyrics-BeamsMelismata.xml", 76);
        $this->fileTest("61i-Lyrics-Chords.xml", 53);
        $this->fileTest("61j-Lyrics-Elisions.xml");
        $this->fileTest("61k-Lyrics-SpannersExtenders.xml");
    }

    public function test71()
    {
        $this->fileTest("71a-Chordnames.xml", 25);
        $this->fileTest("71c-ChordsFrets.xml", 25);
        $this->fileTest("71d-ChordsFrets-Multistaff.xml", 21);
        $this->fileTest("71e-TabStaves.xml", 51);
        $this->fileTest("71f-AllChordTypes.xml", 13);
        $this->fileTest("71g-MultipleChordnames.xml", 32);
    }

    public function test72()
    {
        $this->fileTest("72a-TransposingInstruments.xml", 64);
        $this->fileTest("72b-TransposingInstruments-Full.xml", 39);
        $this->fileTest("72c-TransposingInstruments-Change.xml", 89);
    }

    public function test73()
    {
        $this->fileTest("73a-Percussion.xml", 65);
    }

    public function test74()
    {
        $this->fileTest("74a-FiguredBass.xml");
    }

    public function test75()
    {
        $this->fileTest("75a-AccordionRegistrations.xml");
    }

    public function test99()
    {
        $this->fileTest("99a-Sibelius5-IgnoreBeaming.xml");
        $this->fileTest("99b-Lyrics-BeamsMelismata-IgnoreBeams.xml", 76);
        $this->fileTest("99c-Wavy-Lines-No-Numbers.xml");
        $this->fileTest("99d-AccordionInvalid.xml");
    }

    /**
     * @brief Test that information from a file isn't lost
     * when it is partified.
     * @param string $fileName The name of the MusicXML test suite file.
     * @param int $percentThreshold Minimum percent match we will tolerate for this file.
     */
    private function fileTest($fileName, $percentThreshold = 90)
    {
        $localPath = "../examplemedia/musicxmlTestSuite/xmlFiles/$fileName";
        Artisan::call("media:create", [ 'file' => $localPath ]);
        $m = Media::orderBy('id', 'desc')->first();

        // Generate the partify musicxml file.
        $getMusicXML = $this->get("/media/{$m->id}/partify.musicxml");
        $getMusicXML->assertStatus(200, "Could not convert $fileName to partify.musicxml");
        // Re-upload the partify musicxml file so we can compare some conversions.
        Artisan::call("media:create", [ 'file' => $m->getAbsPath("partify.musicxml") ]);
        $m2 = Media::orderBy('id', 'desc')->first();

        $midiOriginal  = $this->get("/media/{$m->id}/harmony.midi");
        $midiConverted = $this->get("/media/{$m2->id}/harmony.midi");

        $midiOriginal->assertStatus(200, "Could not generate midi for $fileName.");
        $midiConverted->assertStatus(200, "Could not generate midi from partify.musicxml ($fileName).");

        $percent = 0;
        similar_text(
            file_get_contents($m->getAbsPath("harmony.midi")),
            file_get_contents($m2->getAbsPath("harmony.midi")),
            $percent
        );
        // Just check that they are similar enough.
        $this->assertGreaterThan($percentThreshold, $percent, $fileName);
    }
}
