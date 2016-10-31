package com.aprivate.selfie;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.RelativeLayout;

import com.aprivate.selfie.login.LoginActivity;
import com.aprivate.selfie.wedget.custom.AbstractActivity;
import com.github.ppamorim.dragger.DraggerPosition;

import java.util.Timer;
import java.util.TimerTask;

/**
 * An example full-screen activity that shows and hides the system UI (i.e.
 * status bar and navigation/system bar) with user interaction.
 */
public class splachscreen extends AbstractActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        RelativeLayout SplachScreen = (RelativeLayout) findViewById(R.id.splach);
        SplachScreen.setSystemUiVisibility(View.SYSTEM_UI_FLAG_LOW_PROFILE
                | View.SYSTEM_UI_FLAG_FULLSCREEN
                | View.SYSTEM_UI_FLAG_LAYOUT_STABLE
                | View.SYSTEM_UI_FLAG_IMMERSIVE_STICKY
                | View.SYSTEM_UI_FLAG_LAYOUT_HIDE_NAVIGATION
                | View.SYSTEM_UI_FLAG_HIDE_NAVIGATION);


        TimerTask timerTask = new TimerTask() {
            @Override
            public void run() {
                startDraggerActivity(DraggerPosition.TOP);
                finish();
            }
        };

        Timer timer = new Timer(true);
        timer.schedule(timerTask, 3000);
    }

    @Override
    protected int getContentViewId() {
        return R.layout.activity_splachscreen;
    }

    private void startDraggerActivity(DraggerPosition dragPosition) {
        Intent intent = new Intent(this, LoginActivity.class);
        intent.putExtra(LoginActivity.DRAG_POSITION, dragPosition);
        startActivityNoAnimation(intent);
    }

    private void startActivityNoAnimation(Intent intent) {
        intent.setFlags(Intent.FLAG_ACTIVITY_NO_ANIMATION);
        startActivity(intent);
    }

}
