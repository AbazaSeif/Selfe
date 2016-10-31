package com.aprivate.selfie.wedget.custom;

import android.os.Handler;

import com.dd.processbutton.ProcessButton;

import java.util.Random;

public class ProgressGenerator {

    public interface OnCompleteListener {

        public void onComplete();
    }

    private OnCompleteListener mListener;
    private int mProgress;

    public ProgressGenerator(OnCompleteListener listener) {
        mListener = listener;
    }
    public ProgressGenerator(){
        mListener = null;
    }

    public void start(final ProcessButton button) {
        final Handler handler = new Handler();
        mProgress = 0;
        handler.postDelayed(new Runnable() {
            @Override
            public void run() {
                mProgress += 10;
                button.setProgress(mProgress);
                if (mProgress < 100) {
                    handler.postDelayed(this, generateDelay());
                } else {
                    if(mListener != null) {
                        mListener.onComplete();
                    }
                }
            }
        }, generateDelay());
    }

    public void stop(final ProcessButton button){
        button.setProgress(100);
        if(mListener != null) {
            mListener.onComplete();
        }
    }

    public void Error(final ProcessButton button){
        button.setProgress(-1);
        mProgress = 100;

    }
    private Random random = new Random();

    private int generateDelay() {
        return random.nextInt(1000);
    }
}
